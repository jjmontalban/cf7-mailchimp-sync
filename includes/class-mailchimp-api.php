<?php

class CF7_MailChimp_API {
    private $api_key;
    private $list_id;

    public function __construct() {
        $this->api_key = get_option('cf7_mc_api_key');
        $this->list_id = get_option('cf7_mc_list_id');
    }

    public function add_subscriber($email, $first_name, $phone, $fecha, $tags = []) {
        if (!$this->api_key || !$this->list_id) {
            error_log('MailChimp API Error: Falta la API Key o el ID de la lista.');
            return false;
        }
    
        $dc = explode('-', $this->api_key)[1];
        $url = "https://$dc.api.mailchimp.com/3.0/lists/{$this->list_id}/members/";
    
        $data = [
            'email_address' => $email,
            'status'        => 'subscribed',
            'merge_fields'  => [
                'FNAME'  => $first_name,
                'MMERGE4' => $phone,
                'MMERGE3' => $fecha
            ],
            'tags'          => $tags
        ];
    
        error_log("MailChimp API - Enviando petición a MailChimp: " . json_encode($data));
    
        $response = wp_remote_post($url, [
            'body'    => json_encode($data),
            'headers' => [
                'Authorization' => 'apikey ' . $this->api_key,
                'Content-Type'  => 'application/json'
            ]
        ]);
        
        if (is_wp_error($response)) {
            error_log("MailChimp API - Error en `wp_remote_post()`: " . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        error_log("MailChimp API - Código de respuesta: $response_code");
        error_log("MailChimp API - Respuesta de MailChimp: " . $response_body);
        
        if ($response_code != 200) {
            error_log('MailChimp API - Error en la solicitud: ' . print_r($response_body, true));
        }
    
        return $response_body;
    }
    
}
