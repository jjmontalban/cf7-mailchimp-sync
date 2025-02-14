<?php

class CF7_MailChimp_Handler {
    public function __construct() {
        add_action('wpcf7_mail_sent', [$this, 'sync_to_mailchimp']);
    }

    public function sync_to_mailchimp($contact_form) {
        $submission = WPCF7_Submission::get_instance();
        if (!$submission) return;

        $posted_data = $submission->get_posted_data();

        // Obtener datos del formulario
        $email = $posted_data['your-email'] ?? '';
        $name = $posted_data['your-name'] ?? '';
        $phone = $posted_data['your-tel'] ?? '';
        $fecha = $posted_data['submission-date'] ?? date('Y-m-d H:i:s');
        $tipo_ayuda = isset($posted_data['services']) ? (array) $posted_data['services'] : [];
        $page_title = $posted_data['page-title'] ?? '';
        $form_id = $contact_form->id();

        error_log("CF7 MailChimp Sync - Datos recibidos: " . print_r($posted_data, true));

        // Bloquear el formulario "Trabaja con Nosotros"
        if ($form_id === '43292f5' || stripos($contact_form->title(), 'Trabaja con nosotros') !== false) {
            error_log("CF7 MailChimp Sync - Este formulario no se sincroniza con MailChimp.");
            return;
        }

        if (empty($email)) {
            error_log('CF7 MailChimp Sync - Error: No se recibió un email.');
            return;
        }

        // Evitar exportar solicitudes de empleo
        if (in_array('Quiero trabajar en Brunimarsa', $tipo_ayuda)) {
            error_log('CF7 MailChimp Sync - No se exporta el usuario porque seleccionó "Quiero trabajar en Brunimarsa".');
            return;
        }

        // Asignar tags correctamente (múltiples opciones)
        $tags = ['PROSPECTOS']; // Siempre lleva este tag
        foreach ($tipo_ayuda as $opcion) {
            $tag_servicio = $this->get_tag_for_service($opcion);
            if ($tag_servicio) {
                $tags[] = $tag_servicio;
            }
        }
        
        error_log("CF7 MailChimp Sync - Tags asignados antes de enviar: " . implode(', ', $tags));

        // Enviar los datos a MailChimp
        $mailchimp = new CF7_MailChimp_API();
        $mailchimp->add_or_update_subscriber($email, $name, $phone, $fecha, $tags);
    }

    private function get_tag_for_service($tipo_ayuda) {
        $tags_map = [
            'Cuidador por horas/externo' => 'CUIDADO',
            'Teleasistencia' => 'TELEASISTENCIA',
            'Servicio doméstico por horas/externo' => 'DOMESTICO',
            'Psicología' => 'PSICOLOGIA',
            'Podología' => 'PODOLOGIA',
            'Fisioterapia' => 'FISIOTERAPIA',
            'Dietas nutricionales personalizadas' => 'NUTRICIONISTA',
            'Entrenador personal' => 'ENTRENADOR',
            'Acompañamiento' => 'ACOMPAÑAMIENTO',
            'Informática' => 'INFORMATICA'
        ];

        return $tags_map[$tipo_ayuda] ?? 'OTRO_SERVICIO';
    }
}
?>