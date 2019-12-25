<?php

$getCompanyName = getCompanyName();

return [
    'sidebar'              => [
        'dashboard'    => 'Tablero',
        'users'        => 'Usuarios',
        'transactions' => 'Actas',
        'settings'     => 'Ajustes',

    ],
    'footer'               => [
        'follow-us'      => 'Síguenos',
        'related-link'   => 'Enlaces relacionados',
        'categories'     => 'Categorías',
        'language'       => 'Idioma',
        'copyright'      => 'Copyright',
        'copyright-text' => 'Todos los derechos reservados',

    ],
    '2sa'                  => [
        'title-short-text'             => 'De vuelta',
        'title-text'                   => 'Autenticación de 2 factores',
        'extra-step'                   => 'Este paso adicional muestra que realmente estás intentando iniciar sesión.',
        'extra-step-settings-verify'   => 'Este paso adicional muestra que realmente estás tratando de verificar.',
        'confirm-message'              => 'Se acaba de enviar un mensaje de texto con un código de autenticación de 6 dígitos a',
        'confirm-message-verification' => 'Se acaba de enviar un mensaje de texto con un código de verificación de 6 dígitos a',
        'remember-me-checkbox'         => 'Recuerdame en este navegador',
        'verify'                       => 'Verificar',

    ],
    'personal-id'          => [
        'title'                 => 'Verificación de identidad',
        'identity-type'         => 'Tipo de identidad',
        'select-type'           => 'Seleccione tipo',
        'driving-license'       => 'Licencia de conducir',
        'passport'              => 'Pasaporte',
        'national-id'           => 'Identificación nacional',
        'identity-number'       => 'Numero de identidad',
        'upload-identity-proof' => 'Subir prueba de identidad',

    ],
    'personal-address'     => [
        'title'                => 'Verificación de dirección',
        'upload-address-proof' => 'Subir prueba de dirección',

    ],
    'google2fa'            => [
        'title-text'     => 'Autenticación de dos factores de Google (2FA)',
        'subheader-text' => 'Escanear el código QR con la aplicación Google Autenticador.',
        'setup-a'        => 'Configure su aplicación Google Autenticador antes de continuar.',
        'setup-b'        => 'No podrás verificar lo contrario.',
        'proceed'        => 'Proceder a la verificación',
        'otp-title-text' => 'Contraseña de una sola vez (OTP)',
        'otp-input'      => 'Ingrese la OTP de 6 dígitos de la aplicación Google Autenticador',

    ],
    'form'                 => [

        'button'                   => [
            'sign-up' => 'Regístrate',
            'login'   => 'Iniciar sesión',

        ],
        'forget-password-form'     => 'Se te olvidó tu contraseña',
        'reset-password'           => 'Restablecer la contraseña',
        'yes'                      => 'Sí',
        'no'                       => 'No',
        'add'                      => 'Añadir nuevo',
        'category'                 => 'Categoría',
        'unit'                     => 'Unidades',
        'category_create'          => 'Crear Categoría',
        'category_edit'            => 'Editar categoria',
        'location_create'          => 'Crear ubicación',
        'location_edit'            => 'Editar ubicación',
        'location_name'            => 'Nombre del lugar',
        'location_code'            => 'Código de localización',
        'delivery_address'         => 'Dirección de entrega',
        'default_loc'              => 'Ubicación predeterminada',
        'phone_one'                => 'Un telefono',
        'phone_two'                => 'Telefono dos',
        'fax'                      => 'Fax',
        'email'                    => 'Email',
        'username'                 => 'Nombre de usuario',
        'contact'                  => 'Contacto',
        'item_create'              => 'Crear artículo',
        'unit_create'              => 'Crear unidad',
        'unit_edit'                => 'Unidad de edición',
        'item_id'                  => 'Identificación del artículo',
        'item_name'                => 'Nombre del árticulo',
        'quantity'                 => 'Cantidad',
        'item_des'                 => 'Descripción del Artículo',
        'picture'                  => 'Imagen',
        'location'                 => 'Ubicación',
        'add_stock'                => 'Añadir stock',
        'select_one'               => 'Seleccione uno',
        'memo'                     => 'Memorándum',
        'close'                    => 'Cerrar',
        'remove_stock'             => 'Eliminar Stock',
        'move_stock'               => 'Mover stock',
        'location_from'            => 'Ubicación desde',
        'location_to'              => 'Ubicación para',
        'item_edit'                => 'Editar elemento',
        'copy'                     => 'Dupdo',
        'store_in'                 => 'Almacenar en',
        'order_items'              => 'Encargar artículos',
        'delivery_from'            => 'Entrega desde la ubicación',
        'user_role_create'         => 'Crear rol de usuario',
        'permission'               => 'Permiso',
        'section_name'             => 'Nombre de la sección',
        'areas'                    => 'Áreas',
        'Add'                      => 'Añadir',
        'Edit'                     => 'Editar',
        'Delete'                   => 'Borrar',
        'name'                     => 'Nombre',
        'full_name'                => 'Nombre completo',
        'password'                 => 'Contraseña',
        'old_password'             => 'Contraseña anterior',
        'set_password'             => 'Configurar la clave',
        'new_password'             => 'Nueva contraseña',
        'update_password'          => 'Actualiza contraseña',
        'confirm_password'         => 'Confirmar contraseña',
        're_password'              => 'Repite la contraseña',
        'change_password'          => 'Cambia la contraseña',
        'settings'                 => 'Ajustes',
        'change_password_form'     => 'Cambiar formulario de contraseña',
        'user_create_form'         => 'Crear usuario',
        'user_update_form'         => 'Actualizar usuario',
        'submit'                   => 'Enviar',
        'update'                   => 'Actualizar',
        'cancel'                   => 'Cancelar',
        'sign_out'                 => 'Desconectar',
        'delete'                   => 'Borrar',
        'company_create'           => 'Crear empresa',
        'company'                  => 'Empresa',
        'db_host'                  => 'Anfitrión',
        'db_user'                  => 'Usuario de la base de datos',
        'db_password'              => 'Contraseña de la base de datos',
        'db_name'                  => 'Nombre de la base de datos',
        'new_company_password'     => 'Nuevo script Admin Password',
        'pdf'                      => 'PDF',
        'customer'                 => 'Cliente',
        'customer_branch'          => 'Sucursal de clientes',
        'payment_type'             => 'Tipo de pago',
        'from_location'            => 'Ubicación',
        'add_item'                 => 'Añadir artículo',
        'sales_invoice_items'      => 'Artículos de factura de ventas',
        'purchase_invoice_items'   => 'Compra artículos de factura',
        'supplier'                 => 'Proveedor',
        'order_item'               => 'Articulo ordenado',
        'order_date'               => 'Orden en',
        'item_tax_type'            => 'Tipo de impuesto',
        'currency'                 => 'Moneda',
        'sales_type'               => 'Tipo de venta',
        'price'                    => 'Precio',
        'supplier_unit_of_messure' => 'Proveedores de la unidad de medida',
        'conversion_factor'        => 'Factor de conversión (a nuestra UOM)',
        'supplier_description'     => 'Código o descripción del proveedor',
        'next'                     => 'Siguiente',
        'add_branch'               => 'Añadir rama',
        'payment_term'             => 'Plazo de pago',
        'site_name'                => 'Nombre del sitio',
        'site_short_name'          => 'Nombre corto del sitio',
        'source'                   => 'Fuente',
        'destination'              => 'Destino',
        'stock_move'               => 'Transferencia de acciones',
        'after'                    => 'Después',
        'status'                   => 'Estado',
        'date'                     => 'Fecha',
        'qty'                      => 'Cantidad',
        'terms'                    => 'Término',
        'add_new_customer'         => 'Añadir nuevo cliente',
        'add_new_order'            => 'Añadir nuevo pedido',
        'add_new_invoice'          => 'Añadir nueva factura',
        'group_name'               => 'Nombre del grupo',
        'edit'                     => 'Editar',
        'title'                    => 'Título',
        'description'              => 'Descripción',
        'reminder'                 => 'Fecha de recordatorio',

    ],
    'home'                 => [

        'title-bar'       => [
            'home'      => 'Casa',
            'send'      => 'Enviar',
            'request'   => 'Solicitud',
            'developer' => 'Desarrollador',
            'login'     => 'Iniciar sesión',
            'register'  => 'Registro',
            'logout'    => 'Cerrar sesión',
            'dashboard' => 'Tablero',

        ],
        'banner'          => [
            'title'      => 'Transferencia de dinero simple a: br sus seres queridos',
            'sub-title1' => 'Simple para: ser Integrar',
            'sub-title2' => 'Múltiple: br billetera',
            'sub-title3' => 'Avanzado: br seguridad',

        ],
        'choose-us'       => [
            'title'      => '¿Por qué elegirnos?',
            'sub-title1' => 'No somos el banco. Con nosotros obtienes tarifas bajas y tasas de cambio en tiempo real.',
            'sub-title2' => 'Obtenga su dinero a familiares y amigos al instante, solo necesita una dirección de correo electrónico.',
            'sub-title3' => 'Para transferir dinero, retiro y cambio de moneda, nuestras tarifas son de bajo costo.',

        ],
        'payment-gateway' => [
            'title' => 'Procesadores de pago',

        ],
        'services'        => [
            't1' => 'API de pago',
            // 's1' => 'Gestionará la experiencia de pago de los clientes mediante la integración de nuestra interfaz API perfecta en su sitio web.',
            's1' => 'Gestionará clientes ' . $getCompanyName . ' experiencia mediante la integración de nuestra interfaz API sin problemas en su sitio web.',
            't2' => 'Pagos en línea',
            's2' => 'Sea cual sea su crédito, débito o cuenta bancaria, puede pagar a su manera.',
            't3' => 'Cambio de divisas',
            's3' => 'Moneda predeterminada a otra puedes cambiarla fácilmente.',
            't4' => 'Solicitud de pago',
            's4' => 'Mediante estos sistemas ahora puede solicitar el pago de dinero de cualquier país a cualquier país.',
            't5' => 'Sistema de vales',
            's5' => 'Emita y gestione sus propios vales de marca o dotados',
            't6' => 'Detección de fraude',
            's6' => 'Significa que ayudamos a mantener su cuenta más segura y confiable. Disfruta de pagos seguros en línea.',

        ],
        'how-work'        => [
            'title'      => 'Cómo funciona',
            'sub-title1' => 'Primero, cree un depósito en su cuenta.',
            'sub-title2' => 'Decida la cantidad que desea enviar y elija la billetera.',
            'sub-title3' => 'Escriba la dirección de correo electrónico con una breve nota si lo desea.',
            'sub-title4' => 'Haga clic en enviar dinero.',
            'sub-title5' => 'Usted puede cambiar su moneda también.',

        ],
    ],
    'send-money'           => [

        'banner'    => [
            'title'     => 'Envía dinero de la manera que más te convenga.',
            'sub-title' => 'Envíe y reciba dinero de forma rápida y sencilla o regale un vale como regalo',
            // 'sign-up'   => 'Regístrese para pagar dinero',
            'sign-up'   => 'Inscribirse a ' . $getCompanyName,
            'login'     => 'Inicia sesión ahora',

        ],
        'section-a' => [
            'title'         => 'Envío global de dinero en pocos minutos con varias divisas Solo en unos pocos
                            Clics',

            'sub-section-1' => [
                'title'     => 'Registrar Cuenta',
                'sub-title' => 'Al principio, sea un usuario registrado, luego inicie sesión en su cuenta e ingrese su tarjeta o banco
                            detalles de la información que se requiere para usted.',

            ],
            'sub-section-2' => [
                'title'     => 'Seleccione su destinatario',
                'sub-title' => 'Ingrese su dirección de correo electrónico del destinatario que no se compartirá con otros y permanecerá segura, luego
                            añadir una cantidad con la moneda para enviar de forma segura.',

            ],
            'sub-section-3' => [
                'title'     => 'Enviar dinero',
                'sub-title' => 'Después de enviar el dinero, el destinatario será notificado por correo electrónico cuando el dinero haya sido
                            transferidos a su cuenta.',

            ],
        ],
        'section-b' => [
            'title'     => 'Enviar dinero dentro de unos segundos.',
            'sub-title' => 'Cualquier persona con una dirección de correo electrónico puede enviar / recibir una solicitud de pago, ya sea que tenga una cuenta o no. Pueden pagar con tarjeta de crédito o cuenta bancaria.',

        ],
        'section-c' => [
            // 'title'     => 'Envíe dinero a cualquier persona, en cualquier lugar, utilizando al instante el sistema Pay Money',
            'title'     => 'Envíe dinero a cualquier persona, en cualquier lugar, utilizando al instante el ' . $getCompanyName. ' sistema',
//             'sub-title' => 'Transfiera fondos a sus amigos y familiares a nivel mundial a través de la aplicación móvil Pay Money, banco
//                             cuenta u otra pasarela de pago. Los fondos van directamente a su cuenta ya sea que el destinatario
//                             Tienes alguna cuenta o no. Puede enviar / solicitar dinero a través de diferentes tipos de pago
//                             Gateway con diferentes monedas.',
            'sub-title' => 'Transfiera fondos a sus amigos y familiares a nivel mundial a través de ' . $getCompanyName . ' Pasarela de pago de aplicaciones móviles, cuentas bancarias u otras. Los fondos van directamente a su cuenta, ya sea que el destinatario tenga o no cuenta. Puede enviar / solicitar dinero a través de diferentes tipos de pasarela de pago con diferentes monedas.',


        ],
        'section-d' => [
            'title'   => 'Más rápido, más sencillo, más seguro: envíe dinero a quienes ama hoy.',
            // 'sign-up' => 'Regístrate para pagar dinero',
            'sign-up' => 'Inscribirse a ' . $getCompanyName,

        ],
        'section-e' => [
            'title'     => 'Empieza a enviar dinero.',
            'sub-title' => 'Ahora, no tienes problemas por tener dinero en efectivo. Cualquiera puede enviar dinero desde su tarjeta, banco
                            cuenta, saldo de paypal u otra vía de pago. Lo notificarás a través de un simple email.',

        ],
    ],
    'request-money'        => [

        'banner'    => [
            // 'title'          => 'Solicite dinero de todo el mundo: br con pago de dinero',
            'title'          => 'Solicite dinero de todo el mundo con pago de dinero ' . $getCompanyName,
            'sub-title'      => 'Haga un recordatorio a la gente para enviar devolución de dinero.',
            // 'sign-up'        => 'Regístrese para pagar dinero',
            'sign-up'        => 'Inscribirse a ' . $getCompanyName,
            'already-signed' => '¿Ya te has registrado?',
            'login'          => 'iniciar sesión',
            'request-money'  => 'para pedir dinero.',

        ],
        'section-a' => [
            'title'         => 'Sistema de solicitud de dinero fácil de usar.',
            'sub-title'     => 'Solicitar dinero es una forma eficiente y educada de pedir el dinero que se le debe. Utilizar ' . $getCompanyName . ' Sistema para enviar dinero, recibir dinero o transferir dinero de los más cercanos y queridos.',

            'sub-section-1' => [
                'title'     => 'Registrar Cuenta',
                'sub-title' => 'Al principio, sea un usuario registrado, luego inicie sesión en su cuenta e ingrese su tarjeta o banco
                            Detalles de información que se requiere para que usted solicite dinero.',

            ],
            'sub-section-2' => [
                'title'     => 'Seleccione su destinatario',
                'sub-title' => 'Entre la dirección de correo electrónico de su destinatario que no se compartirá con otras personas y permanecerá protegida, luego
                            añadir una cantidad con la moneda para enviar de forma segura.',

            ],
            'sub-section-3' => [
                'title'     => 'Pedir dinero',
                'sub-title' => 'Después de solicitar dinero, el destinatario será notificado por correo electrónico cuando el dinero haya sido
                            transferidos de su cuenta.',

            ],
        ],
        'section-b' => [
            'title'     => 'Puede enviar dinero por teléfono móvil',
            'sub-title' => 'Ahora, no tienes problemas por tener dinero en efectivo. Cualquiera puede enviar dinero desde su tarjeta, banco
                            cuenta, saldo de paypal u otra vía de pago. Lo notificarás a través de un simple email.',

        ],
        'section-c' => [
            // 'title'     => 'Utilice la aplicación móvil payMoney para solicitar dinero fácilmente.',
            'title'     => 'Utilizar el ' . $getCompanyName . ' Aplicación móvil para solicitar dinero fácilmente.',
            'sub-title' => 'Cualquier persona con una dirección de correo electrónico puede recibir una solicitud de pago, ya sea que tenga una cuenta o no. Te pueden pagar con PayPal, franja, 2checkout y muchos más pasillos de pago.',

        ],
        'section-d' => [
            // 'title'     => 'Solicite dinero a cualquier persona, en cualquier lugar, utilizando al instante el sistema payMoney',
            'title'     => 'Solicite dinero a cualquier persona, en cualquier lugar, utilizando al instante el '. $getCompanyName .' sistema ',
//             'sub-title' => 'Transfiera fondos a sus amigos y familiares a nivel mundial a través de la aplicación móvil payMoney, banco
//                             Cuenta u otra pasarela de pago. Los fondos van directamente a su cuenta si el destinatario
//                             Tienes alguna cuenta o no. Puede enviar / solicitar dinero a través de diferentes tipos de pago
//                             Gateway con diferentes monedas.',
            'sub-title' => 'Transfiera fondos a sus amigos y familiares a nivel mundial a través de ' . $getCompanyName . ' Aplicación móvil, cuenta bancaria u otra pasarela de pago. Los fondos van directamente a su cuenta, ya sea que el destinatario tenga alguna cuenta o no. Puede enviar / solicitar dinero a través de diferentes tipos de pasarela de pago con diferentes monedas.',

        ],
        'section-e' => [
            'title'   => 'Más rápido, más sencillo, más seguro: envíe dinero a quienes ama hoy.',
            // 'sign-up' => 'Regístrese para pagar dinero',
            'sign-up' => 'Inscribirse a ' . $getCompanyName,

        ],
    ],
    'login'                => [
        'title'           => 'Iniciar sesión',
        'form-title'      => 'Registrarse',
        'email'           => 'Email',
        'phone'           => 'Teléfono',
        'email_or_phone'  => 'Email o teléfono',
        'password'        => 'Contraseña',
        'forget-password' => '¿Contraseña olvidada?',
        'no-account'      => '¿No tienes una cuenta?',
        'sign-up-here'    => 'Registrate aquí',

    ],
    'registration'         => [
        'title'                => 'Registro',
        'form-title'           => 'Crear nuevo usuario',
        'first-name'           => 'Nombre de pila',
        'last-name'            => 'Apellido',
        'email'                => 'Email',
        'phone'                => 'Teléfono',
        'password'             => 'Contraseña',
        'confirm-password'     => 'Confirmar contraseña',
        'terms'                => 'Al hacer clic en Registrarse, acepta nuestros Términos, Política de datos y Política de cookies.',
        'new-account-question' => '¿Ya tienes una cuenta?',
        'sign-here'            => 'Firme aquí',
        'type-title'           => 'Tipo',
        'type-user'            => 'Usuario',
        'type-merchant'        => 'Comerciante',
        'select-user-type'     => 'Seleccione el tipo de usuario',

    ],
    'dashboard'            => [

        'nav-menu'     => [
            'dashboard'    => 'Tablero',
            'transactions' => 'Actas',
            'send-req'     => 'Enviar petición',
            'send-to-bank' => 'Enviar al banco',
            'vouchers'     => 'Vales',
            'merchants'    => 'Mercantes',
            'disputes'     => 'Disputas',
            'settings'     => 'Ajustes',
            'tickets'      => 'Entradas',
            'logout'       => 'Cerrar sesión',
            'payout'       => 'Pagar',
            'exchange'     => 'Intercambiar',

        ],
        'left-table'   => [
            'title'            => 'Actividad reciente',
            'date'             => 'Fecha',
            'description'      => 'Descripción',
            'status'           => 'Estado',
            'currency'         => 'Moneda',
            'amount'           => 'Cantidad',
            'view-all'         => 'Ver todo',
            'no-transaction'   => 'No se ha encontrado ninguna transacción!',
            'details'          => 'Detalles',
            'fee'              => 'Cuota',
            'total'            => 'Total',
            'transaction-id'   => 'ID de transacción',
            'transaction-date' => 'Fecha de Transacción',

            'deposit'          => [
                'deposited-to'     => 'Depositado en',
                'payment-method'   => 'Método de pago',
                'deposited-amount' => 'Cantidad depositada',
                'deposited-via'    => 'Depositado vía',

            ],
            'withdrawal'       => [
                'withdrawan-with'   => 'Pago con',
                'withdrawan-amount' => 'Cantidad a pagar',

            ],
            'transferred'      => [
                'paid-with'          => 'Pagado con',
                'transferred-amount' => 'Cantidad transferida',
                'email'              => 'Email',
                'note'               => 'Nota',

            ],
            'bank-transfer'    => [
                'bank-details'        => 'Detalles del banco',
                'bank-name'           => 'Nombre del banco',
                'bank-branch-name'    => 'Nombre de la rama',
                'bank-account-name'   => 'Nombre de la cuenta',
                'bank-account-number' => 'Número de cuenta', //pm1.9
                'transferred-with'    => 'Transferido con',
                'transferred-amount'  => 'Importe de transferencia bancaria',

            ],
            'received'         => [
                'paid-by'         => 'Pagado por',
                'received-amount' => 'Cantidad recibida',

            ],
            'exchange-from'    => [
                'from-wallet'          => 'De la cartera',
                'exchange-from-amount' => 'Cantidad de intercambio',
                'exchange-from-title'  => 'Intercambio desde',
                'exchange-to-title'    => 'Intercambio a',

            ],
            'exchange-to'      => [
                'to-wallet' => 'A la cartera',

            ],
            'voucher-created'  => [
                'voucher-code'   => 'Código de cupón',
                'voucher-amount' => 'Importe del vale',

            ],
            'request-to'       => [
                'accept' => 'Aceptar',

            ],
            'payment-Sent'     => [
                'payment-amount' => 'Monto del pago',

            ],
        ],
        'right-table'  => [
            'title'                => 'Carteras',
            'no-wallet'            => 'No se encontró billetera!',
            'default-wallet-label' => 'Defecto',

        ],
        'button'       => [
            'deposit'         => 'Depositar',
            'withdraw'        => 'Pagar',
            'payout'          => 'Pagar',
            'exchange'        => 'Intercambiar',
            'submit'          => 'Enviar',
            'send-money'      => 'Enviar dinero',
            'send-request'    => 'Enviar petición',
            'create'          => 'Crear',
            'activate'        => 'Activar',
            'new-merchant'    => 'Nuevo comerciante',
            'details'         => 'Detalles',
            'change-picture'  => 'Cambiar imagen',
            'change-password' => 'Cambia la contraseña',
            'new-ticket'      => 'Nuevo ticket',
            'next'            => 'Siguiente',
            'back'            => 'Espalda',
            'confirm'         => 'Confirmar',
            'select-one'      => 'Seleccione uno',
            'update'          => 'Actualizar',
            'filter'          => 'Filtrar',

        ],
        'deposit'      => [
            'title'                                       => 'Depositar',
            'deposit-via'                                 => 'Depositar dinero via',
            'amount'                                      => 'Cantidad',
            'currency'                                    => 'Moneda',
            'payment-method'                              => 'Método de pago',
            'no-payment-method'                           => 'Método de pago no encontrado!',
            'fees-limit-payment-method-settings-inactive' => 'Los ajustes de Límite y Método de pago están inactivos',
            'total-fee'                                   => 'Total Fee:',
            'total-fee-admin'                             => 'Total:',
            'fee'                                         => 'Cuota',
            'deposit-amount'                              => 'Cantidad del depósito',
            'completed-success'                           => 'Depósito completado con éxito',
            'success'                                     => 'Éxito',
            'deposit-again'                               => 'Depositar dinero otra vez',

            'deposit-stripe-form'                         => [
                'title'   => 'Depósito con raya',
                'card-no' => 'Número de tarjeta',
                'mm-yy'   => 'MM / YY',
                'cvc'     => 'CVC',

            ],
            'select-bank'                                 => 'Seleccionar banco', //pm1.9
            'payment-references'                          => [
                // 'merchant-payment-reference'   => 'Referencia de pago del comerciante',
                'user-payment-reference' => 'Referencia de pago del usuario',
                // 'merchant-payment-number'   => 'Número de pago del comerciante',
            ],
        ],
        'payout'       => [

            'menu'           => [
                'payouts'        => 'Pagos',
                'payout-setting' => 'Configuración de pago',
                'new-payout'     => 'Nuevo pago',

            ],
            'list'           => [
                'method'      => 'Método',
                'method-info' => 'Información del método',
                'charge'      => 'Cargar',
                'amount'      => 'Cantidad',
                'currency'    => 'Moneda',
                'status'      => 'Estado',
                'date'        => 'Fecha',
                'not-found'   => 'Datos no encontrados !',
                'fee'         => 'Cuota',

            ],
            'payout-setting' => [
                'add-setting' => 'Añadir configuración',
                'payout-type' => 'Tipo de pago',
                'account'     => 'Cuenta',
                'action'      => 'Acción',

                'modal'       => [
                    'title'                        => 'Añadir configuración de pago',
                    'payout-type'                  => 'Tipo de pago',
                    'email'                        => 'Email',
                    'bank-account-holder-name'     => 'Nombre del propietario de la cuenta bancaria',
                    'branch-name'                  => 'Nombre de la rama',
                    'account-number'               => 'Número de cuenta bancaria / IBAN',
                    'branch-city'                  => 'Branch City',
                    'swift-code'                   => 'Código SWIFT',
                    'branch-address'               => 'Dirección de sucursal',
                    'bank-name'                    => 'Nombre del banco',
                    'attached-file'                => 'Archivo adjunto',
                    'country'                      => 'País',
                    'perfect-money-account-number' => 'Número de cuenta de dinero perfecto',
                    'payeer-account-number'        => 'Número de cuenta Payeer',

                ],
            ],
            'new-payout'     => [
                'title'          => 'Pagar',
                'payment-method' => 'Método de pago',
                'currency'       => 'Moneda',
                'amount'         => 'Cantidad',
                'bank-info'      => 'Información de la cuenta bancaria',
                'withdraw-via'   => 'Usted está a punto de pagar dinero a través de',
                'success'        => 'Éxito',
                'payout-success' => 'Pago completado con éxito',
                'payout-again'   => 'Pago de nuevo',

            ],
        ],
        'confirmation' => [
            'details' => 'Detalles',
            'amount'  => 'Cantidad',
            'fee'     => 'Cuota',
            'total'   => 'Total',

        ],
        'transaction'  => [
            'date-range'      => 'Elige un rango de fechas',
            'all-trans-type'  => 'Todo tipo de transacción',
            'payment-sent'    => 'Pago enviado',
            'payment-receive' => 'Pago recibido',
            'payment-req'     => 'Solicitud de pago',
            'exchanges'       => 'Intercambios',
            'all-status'      => 'Todo el estado',
            'all-currency'    => 'Toda la moneda',
            'success'         => 'Éxito',
            'pending'         => 'Pendiente',
            'blocked'         => 'Cancelado',
            'refund'          => 'Reintegrado',
            'open-dispute'    => 'discusion abierta',

        ],
        'exchange'     => [

            'left-top'    => [
                'title'           => 'Cambio de divisas',
                'select-wallet'   => 'Seleccionar billetera',
                'amount-exchange' => 'Cantidad de intercambio',
                'give-amount'     => 'Tú daras',
                'get-amount'      => 'Conseguirás',
                'balance'         => 'Balance',
                'from-wallet'     => 'De la cartera',
                'to-wallet'       => 'A la cartera',
                'base-wallet'     => 'De la cartera',
                'other-wallet'    => 'A la cartera',
                'type'            => 'Tipo de cambio',
                'type-text'       => 'La moneda base es:',
                'to-other'        => 'A otra moneda',
                'to-base'         => 'A la moneda base',

            ],
            'left-bottom' => [
                'title'            => 'Cambio de moneda (a la moneda base)',
                'exchange-to-base' => 'Intercambio a base',
                'wallet'           => 'Billetera',

            ],
            'right'       => [
                'title' => 'Tipo de cambio',

            ],
            'confirm'     => [
                'title'                => 'Cambio de moneda',
                'exchanging'           => 'Intercambiando',
                'of'                   => 'de',
                'equivalent-to'        => 'Equivalente a',
                'exchange-rate'        => 'Tipo de cambio',
                'amount'               => 'Cantidad de intercambio',
                'has-exchanged-to'     => 'ha cambiado a',
                'exchange-money-again' => 'Cambiar dinero otra vez',

            ],
        ],
        'send-request' => [

            'menu'         => [
                'send'    => 'Enviar',
                'request' => 'Solicitud',

            ],
            'send'         => [
                'title'        => 'Enviar dinero',

                'confirmation' => [
                    'title'              => 'Enviar dinero',
                    'send-to'            => 'Estás enviando dinero a',
                    'transfer-amount'    => 'Monto de la transferencia',
                    'money-send'         => 'Dinero transferido exitosamente',
                    'bank-send'          => 'Dinero transferido al banco exitosamente',
                    'send-again'         => 'Enviar dinero otra vez',
                    'send-to-bank-again' => 'Transferencia al banco otra vez',

                ],
            ],
            'send-to-bank' => [
                'title'        => 'Transferencia al banco',
                'subtitle'     => 'Transferir dinero al banco',

                'confirmation' => [
                    'title'           => 'Transferir dinero al banco',
                    'send-to'         => 'Estás enviando dinero a',
                    'transfer-amount' => 'Monto de la transferencia',
                    'money-send'      => 'Dinero transferido exitosamente',
                    'send-again'      => 'Enviar dinero de nuevo',

                ],
            ],
            'request'      => [
                'title'        => 'Pedir dinero',

                'confirmation' => [
                    'title'              => 'Pedir dinero',
                    'request-money-from' => 'Estás solicitando dinero de',
                    'requested-amount'   => 'Monto requerido',
                    'success'            => 'Éxito',
                    'success-send'       => 'Solicitud de dinero enviada con éxito',
                    'request-amount'     => 'Cantidad de la solicitud',
                    'request-again'      => 'Solicitar dinero otra vez',

                ],
                'success'      => [
                    'title'            => 'Aceptar solicitud de dinero',
                    'request-complete' => 'Dinero solicitado aceptado con éxito',
                    'accept-amount'    => 'Cantidad aceptada',

                ],
                'accept'       => [
                    'title' => 'Aceptar solicitud de pago',

                ],
            ],
            'common'       => [
                'recipient'   => 'Recipiente',
                'amount'      => 'Cantidad',
                'currency'    => 'Moneda',
                'note'        => 'Nota',
                'anyone-else' => 'Nunca compartiremos su correo electrónico con nadie más.',
                'enter-note'  => 'Entrar en nota',
                'enter-email' => 'Ingrese correo electrónico',

            ],
        ],
        'vouchers'     => [

            'left-top'            => [
                'title'    => 'Crear cupón',
                'amount'   => 'Cantidad',
                'currency' => 'Moneda',

            ],
            'left-bottom'         => [
                'title' => 'Activar el cupón',
                'code'  => 'Código',

            ],
            'right'               => [
                'title'     => 'Vales',
                'code'      => 'Código',
                'amount'    => 'Cantidad',
                'status'    => 'Estado',
                'not-found' => '¡No se encontró el cupón!',

            ],
            'confirmation'        => [
                'title'     => 'Crear cupón',
                'sub-title' => 'Estás a punto de crear el vale',
                'amount'    => 'Importe del vale',

            ],
            'active-confirmation' => [
                'title'     => 'Activado avalado',
                'sub-title' => 'Estás a punto de activar el código de cupón',
                'amount'    => 'Importe del vale',

            ],
            'success'             => [
                'title'   => 'Vale',
                'success' => 'Éxito',
                'amount'  => 'Importe del vale',
                'print'   => 'Impresión',

            ],
            'ajax-response'       => [
                'voucher-code-error'         => '¡El cupón ya está activado!',
                'voucher-code-pending-error' => 'Lo sentimos, no se puede activar el cupón pendiente!',
                'voucher-not-found'          => 'Lo sentimos, el cupón no encontrado!',

            ],
        ],
        'merchant'     => [

            'menu'                => [
                'merchant'      => 'Mercantes',
                'payment'       => 'Pagos',
                'list'          => 'Lista',
                'details'       => 'Detalles',
                'edit-merchant' => 'Edit Merchant',
                'new-merchant'  => 'Nuevo comerciante',

            ],
            'table'               => [
                'id'            => 'CARNÉ DE IDENTIDAD',
                'business-name' => 'Nombre del Negocio',
                'site-url'      => 'Sitio URL',
                'type'          => 'Tipo',
                'status'        => 'Estado',
                'action'        => 'Acción',
                'not-found'     => 'Datos no encontrados !',
                'moderation'    => 'Moderación',
                'disapproved'   => 'Desaprobado',
                'approved'      => 'Aprobado',

            ],
            'html-form-generator' => [
                'title'             => 'Generador de formularios HTML',
                'merchant-id'       => 'Identificación del comerciante',
                'item-name'         => 'Nombre del árticulo',
                'order-number'      => 'Número de orden',
                'price'             => 'Precio',
                'custom'            => 'Personalizado',
                'right-form-title'  => 'Ejemplo de formulario HTML',
                'right-form-copy'   => 'Dupdo',
                'right-form-copied' => 'Copiado',
                'right-form-footer' => 'Copie el código del formulario y colóquelo en su sitio web.',
                'close'             => 'Cerrar',
                'generate'          => 'Generar',
                'app-info'          => 'Informacion de la applicacion',
                'client-id'         => 'Identificación del cliente',
                'client-secret'     => 'Secreto del cliente',

            ],
            'payment'             => [
                'merchant'   => 'Comerciante',
                'method'     => 'Método',
                'order-no'   => 'Order no',
                'amount'     => 'Cantidad',
                'fee'        => 'Cuota',
                'total'      => 'Total',
                'currency'   => 'Moneda',
                'status'     => 'Estado',
                'created-at' => 'Fecha',
                'pending'    => 'Pendiente',
                'success'    => 'Éxito',
                'block'      => 'Bloquear',
                'refund'     => 'Reembolso',

            ],
            'add'                 => [
                'title'    => 'Crear comerciante',
                'name'     => 'Nombre',
                'site-url' => 'Sitio URL',
                'type'     => 'Tipo',
                'note'     => 'Nota',
                'logo'     => 'Logo',

            ],
            'details'             => [
                'merchant-id'   => 'Identificación del comerciante',
                'business-name' => 'Nombre del Negocio',
                'status'        => 'Estado',
                'site-url'      => 'Sitio URL',
                'note'          => 'Nota',
                'date'          => 'Fecha',

            ],
            'edit'                => [
                'comment-for-administration' => 'Comentario para la administración',

            ],
        ],
        'dispute'      => [
            'dispute'        => 'Disputas',
            'title'          => 'Título',
            'dispute-id'     => 'ID de disputa',
            'transaction-id' => 'ID de transacción',
            'created-at'     => 'Creado en',
            'status'         => 'Estado',
            'no-dispute'     => '¡Datos no encontrados!',
            'defendant'      => 'Acusado',
            'claimant'       => 'Demandante',
            'description'    => 'Descripción',

            'status-type'    => [
                'open'   => 'Abierto',
                'solved' => 'Resuelto',
                'closed' => 'Cerrado',
                'solve'  => 'Resolver',
                'close'  => 'Cerrar',

            ],
            'discussion'     => [

                'sidebar' => [
                    'title-text'    => 'Información de la disputa',
                    'header'        => 'Información de la disputa',
                    'title'         => 'Título',
                    'reason'        => 'Razón',
                    'change-status' => 'Cambiar Estado',

                ],
                'form'    => [
                    'title'   => 'Ver Disputa',
                    'message' => 'Mensaje',
                    'file'    => 'Expediente',

                ],
            ],
        ],
        'setting'      => [
            'title'                   => 'Perfil del usuario',
            'change-avatar'           => 'Cambiar avatar',
            'change-avatar-here'      => 'Puedes cambiar avatar aquí',
            'change-password'         => 'Cambia la contraseña',
            'change-password-here'    => 'Puedes cambiar la contraseña aquí',
            'profile-information'     => 'información del perfil',
            'email'                   => 'Email',
            'first-name'              => 'Nombre de pila',
            'last-name'               => 'Apellido',
            'mobile'                  => 'Mobile No',
            'address1'                => 'Dirección 1',
            'address2'                => 'Dirección 2',
            'city'                    => 'Ciudad',
            'state'                   => 'Estado',
            'country'                 => 'País',
            'timezone'                => 'Zona horaria',
            'old-password'            => 'Contraseña anterior',
            'new-password'            => 'Nueva contraseña',
            'confirm-password'        => 'Confirmar contraseña',
            'add-phone'               => 'Añadir teléfono',
            'add-phone-subhead1'      => 'Haga clic en',
            'add-phone-subhead2'      => 'para agregar telefono',
            'add-phone-subheadertext' => 'Ingrese el número que desea usar',
            'get-code'                => 'Obtener Código',
            'phone-number'            => 'Número de teléfono',
            'edit-phone'              => 'Editar Teléfono',
            'default-wallet'        => 'Monedero predeterminado',

        ],
        'ticket'       => [
            'title'     => 'Entradas',
            'ticket-no' => 'Ticket No',
            'subject'   => 'Tema',
            'status'    => 'Estado',
            'priority'  => 'Prioridad',
            'date'      => 'Fecha',
            'action'    => 'Acción',
            'no-ticket' => '¡Datos no encontrados!',

            'add'       => [
                'title'    => 'Nuevo ticket',
                'name'     => 'Nombre',
                'message'  => 'Mensaje',
                'priority' => 'Prioridad',

            ],
            'details'   => [

                'sidebar' => [
                    'header'    => 'información de entradas',
                    'ticket-id' => 'Identificación de entradas',
                    'subject'   => 'Tema',
                    'date'      => 'Fecha',
                    'priority'  => 'Prioridad',
                    'status'    => 'Estado',

                ],
                'form'    => [
                    'title'   => 'Ver Ticket',
                    'message' => 'Mensaje',
                    'file'    => 'Expediente',

                ],
            ],
        ],
    ],
    'express-payment'      => [
        'payment'           => 'Pago',
        'pay-with'          => 'Pagar con',
        'about-to-make'     => 'Usted está a punto de hacer el pago a través de',
        'test-payment-form' => 'Formulario de pago de prueba',
        'pay-now'           => '¡Pague ahora!',
    ],

    'express-payment-form' => [
        'merchant-not-found'   => '¡Comerciante no encontrado! Por favor intente con un comerciante válido.',
        'merchant-found'       => 'El destinatario se sometió a una verificación especial y confirmó su fiabilidad.',
        'continue'             => 'Continuar',
        'email'                => 'Email',
        'password'             => 'Contraseña',
        'cancel'               => 'Cancelar',
        'go-to-payment'        => 'Ir al pago',
        'payment-agreement'    => 'El pago se realiza en una página segura. Al realizar un pago, usted acepta los Términos del Acuerdo',
        'debit-credit-card'    => 'Tarjeta de crédito / débito',
        'merchant-payment'     => 'Pago al comerciante',
        'sorry'                => '¡Lo siento!',
        'payment-unsuccessful' => 'Pago sin éxito.',
        'success'              => '¡Éxito!',                   //
        'payment-successfull'  => 'Pago completado con éxito.', //
        'back-home'            => 'Volver a casa',
    ],
];
