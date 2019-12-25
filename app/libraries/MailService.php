<?php
namespace App\libraries;

use Mail;

class MailService
{

    public function send(array $data, $htmlTemplate, $textTemplate = null)
    {
        $template = $htmlTemplate ? array($htmlTemplate, $textTemplate) : array('text' => $textTemplate);
        Mail::send($template, $data, function ($message) use ($data)
        {
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                    $message->{$key}($value[0], @$value[1]);
                }
                else
                {
                    try {
                        $message->{$key}($value);
                    }
                    catch (\ErrorException $e)
                    {
                        $message->{$key} = $value;
                    }
                }
            }
        });
    }
}
