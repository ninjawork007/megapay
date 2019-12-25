<?php

namespace Infoamin\Installer\Http\Controllers;

use Illuminate\Http\Request;
use Infoamin\Installer\Helpers\PermissionsChecker;
use Infoamin\Installer\Helpers\RequirementsChecker;
use Validator;

class PermissionsController extends PermissionsChecker
{

    /**
     * @var PermissionsChecker
     */
    protected $permissions;
    /**
     * @var RequirementsChecker
     */
    protected $requirements;
    /**
     * @param PermissionsChecker $checker && @param RequirementsChecker $requirementschecker
     */
    public function __construct(PermissionsChecker $checker, RequirementsChecker $requirementschecker)
    {
        $this->permissions  = $checker;
        $this->requirements = $requirementschecker;
    }

    /**
     * Display the permissions check page.
     *
     * @return \Illuminate\View\View
     */
    public function checkPermissions()
    {
        $phpSupportInfo = $this->requirements->checkPHPversion(config('installer.core.minimumPhpVersion'));
        $requirements   = $this->requirements->check(config('installer.requirements'));
        $permissions    = $this->permissions->checkPermission(config('installer.permissions'));
        if (!isset($requirements['errors']) && $phpSupportInfo['supported'])
        {
            return view('vendor.installer.permissions', compact('permissions'));
        }
        else
        {
            return redirect('install/requirements');
        }
    }

    /**
     * Display the purchase code verification page.
     *
     * @return \Illuminate\View\View
     */
    public function verifyPurchaseCode(Request $request)
    {

        if ($request->method() != 'POST')
        {
            return view('vendor.installer.purchasecode');
        }
        else
        {
            // dd($request->all());
            $rules = [
                'envatopurchasecode' => 'required',
            ];
            $fieldNames = [
                'envatopurchasecode' => 'Purchase code',
            ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $domainName     = request()->getHost();
                $domainIp       = request()->ip();
                $purchaseStatus = $this->getPurchaseStatus($domainName, $domainIp, $request->envatopurchasecode);
                if ($purchaseStatus == 1)
                {
                    return redirect('install/database');
                }
                else
                {
                    return back()->withErrors(['envatopurchasecode' => 'Invalid purchase code'])->withInput();
                }
            }
        }

    }

    //Send data to verify envato purchase code
    public function getPurchaseStatus($domainName, $domainIp, $envatopurchasecode)
    {
        $data = array(
            'domain_name'        => $domainName,
            'domain_ip'          => $domainIp,
            'envatopurchasecode' => $envatopurchasecode,
        );

        // $url = "http://envatoapi.techvill.net/";
        $url = "https://envatoapi.techvill.org/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        $output = curl_exec($ch);
        if ($output == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}
