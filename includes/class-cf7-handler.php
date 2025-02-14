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
        $tipo_ayuda = isset($posted_data['services']) ? $posted_data['services'][0] : null;
        $page_title = $posted_data['page-title'] ?? '';
        $form_id = $contact_form->id(); // Obtiene el ID del formulario

        error_log("CF7 MailChimp Sync - Datos recibidos: " . print_r($posted_data, true));
        error_log("CF7 MailChimp Sync - Formulario enviado: ID = " . $form_id . ", Título = " . $contact_form->title());

        // Bloquear el formulario "Trabaja con Nosotros" basado en ID o título
        if ($form_id === '43292f5') {
            error_log("CF7 MailChimp Sync - Este formulario no se sincroniza con MailChimp.");
            return;
        }

        if (stripos($contact_form->title(), 'Trabaja con nosotros') !== false) {
            error_log("CF7 MailChimp Sync - Este formulario no se sincroniza con MailChimp.");
            return;
        }        

        // Si no se encuentra en los datos de CF7, intentamos recuperarlo de $_POST
        if (!$tipo_ayuda && isset($_POST['services'])) {
            $tipo_ayuda = is_array($_POST['services']) ? $_POST['services'][0] : $_POST['services'];
            error_log("CF7 MailChimp Sync - Recuperado 'services' desde POST: " . $tipo_ayuda);
        }

        if (empty($email)) {
            error_log('CF7 MailChimp Sync - Error: No se recibió un email.');
            return;
        }

        // Si el usuario selecciona "Quiero trabajar en Brunimarsa", no se exporta
        if ($tipo_ayuda === 'Quiero trabajar en Brunimarsa') {
            error_log('CF7 MailChimp Sync - No se exporta el usuario porque seleccionó "Quiero trabajar en Brunimarsa".');
            return;
        }

        // Asignar tags
        $tags = ['PROSPECTOS']; // Siempre lleva este tag
        $tag_servicio = $this->get_tag_for_service($tipo_ayuda, $_SERVER['REQUEST_URI'], $page_title);

        if ($tag_servicio) {
            $tags[] = $tag_servicio;
        }

        error_log("CF7 MailChimp Sync - Tags asignados antes de enviar: " . implode(', ', $tags));

        // Enviar los datos a MailChimp
        $mailchimp = new CF7_MailChimp_API();
        $mailchimp->add_subscriber($email, $name, $phone, $tags);
    }

    private function get_tag_for_service($tipo_ayuda, $pagina_url, $page_title) {
        $tags_map = [
            'Cuidador por horas/externo' => 'CUIDADO',
            'Cuidador noches' => 'CUIDADO',
            'Cuidador fines de semana' => 'CUIDADO',
            'Cuidador interno' => 'CUIDADO',
            'Cuidador personas dependientes' => 'CUIDADO',
            'Servicio de Ayuda a domicilio (SAD)' => 'CUIDADO',
            'Teleasistencia' => 'TELEASISTENCIA',
            'Servicio doméstico por horas/externo' => 'DOMESTICO',
            'Servicio doméstico interno' => 'DOMESTICO',
            'Servicio doméstico externo' => 'DOMESTICO',
            'Servicio doméstico vacaciones' => 'DOMESTICO',
            'Servicio doméstico fines de semana' => 'DOMESTICO',
            'Psicología' => 'PSICOLOGIA',
            'Podología' => 'PODOLOGIA',
            'Fisioterapia' => 'FISIOTERAPIA',
            'Dietas nutricionales personalizadas' => 'NUTRICIONISTA',
            'Entrenador personal' => 'ENTRENADOR',
            'Acompañamiento' => 'ACOMPAÑAMIENTO',
            'Informática' => 'INFORMATICA'
        ];

        error_log("CF7 MailChimp Sync - Evaluando TAG para servicio: " . $tipo_ayuda);

        if (!empty($tipo_ayuda) && isset($tags_map[$tipo_ayuda])) {
            error_log("CF7 MailChimp Sync - TAG asignado según servicio: " . $tags_map[$tipo_ayuda]);
            return $tags_map[$tipo_ayuda];
        }

        // Si no se seleccionó servicio, intentamos asignar tag según la URL
        $tags_por_url = [
            '/cuidado-de-mayores/' => 'CUIDADO',
            '/teleasistencia/' => 'TELEASISTENCIA',
            '/servicio-domestico/' => 'DOMESTICO',
            '/servicios-complementarios/psicologia/' => 'PSICOLOGIA',
            '/servicios-complementarios/fisioterapeuta/' => 'FISIOTERAPIA',
            '/servicios-complementarios/dietas-personalizadas-mayores/' => 'NUTRICIONISTA',
            '/servicios-complementarios/entrenador-personal-mayores/' => 'ENTRENADOR',
            '/servicios-complementarios/acompanamiento/' => 'ACOMPAÑAMIENTO',
            '/servicios-complementarios/informatica-domicilio-mayores/' => 'INFORMATICA'
        ];

        foreach ($tags_por_url as $url_parcial => $tag) {
            if (strpos($pagina_url, $url_parcial) !== false) {
                error_log("CF7 MailChimp Sync - TAG asignado según URL: $tag");
                return $tag;
            }
        }

        // Si no hay URL definida, intentar asignar el tag según el título de la página
        // Intentar asignar el tag según el título de la página (búsqueda parcial)
        $tags_por_titulo = [
            'Cuidado' => 'CUIDADO',
            'Alzheimer' => 'ALZHEIMER',
            'Parkinson' => 'PARKINSON',
            'Ictus' => 'ICTUS',
            'Teleasistencia' => 'TELEASISTENCIA',
            'Servicio doméstico' => 'DOMESTICO',
            'Psicología' => 'PSICOLOGIA',
            'Fisioterapia' => 'FISIOTERAPIA',
            'Podología' => 'PODOLOGIA',
            'Dietas' => 'NUTRICIONISTA',
            'Entrenador' => 'ENTRENADOR',
            'Acompañamiento' => 'ACOMPAÑAMIENTO',
            'Informática' => 'INFORMATICA'
        ];

        foreach ($tags_por_titulo as $palabra_clave => $tag) {
            if (stripos($page_title, $palabra_clave) !== false) {
                error_log("CF7 MailChimp Sync - TAG asignado según Page Title: $tag");
                return $tag;
            }
        }


        error_log("CF7 MailChimp Sync - No se encontró tag para el tipo de ayuda, URL ni título.");
        return 'OTRO_SERVICIO';

    }
}
