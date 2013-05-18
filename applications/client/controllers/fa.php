<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fa extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            if (is_ajax()) {
                echo 'Your session has timed out due to inactivity. Please log back in to continue.';
                exit();
            }
            else
            {
                redirect('http://' . $this->input->server('HTTP_HOST') . '/login/');
            }
        }
        else
            if (!$this->session->userdata('fa_type')) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    function ajax_delete_additional_fee() {

        $this->load->model('estimates_model');
        $additional_fee_id = $this->input->post('ajax_delete_additional_fee');

        $additional_fee_data = $this->estimates_model->get_case_country_additional_fees_for_invoice(array(
            'additional_fee_id' => $additional_fee_id
        ));

        $associate_data = $this->estimates_model->get_cases_associates_data(array(
            'id' => $additional_fee_data->cases_associates_data_id
        ));

        if ($associate_data->associate_id != $this->session->userdata('fa_user_id')) {
            return false;
        }

        $this->estimates_model->delete_cases_associates_data(array(
            'additional_fee_id' => $additional_fee_id
        ));

    }

    public function index()
    {
        $this->output->enable_profiler(false);
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');

        $header['selected_menu'] = 'dashboard';
        $header['page_name'] = 'Dashboard';
        $header['breadcrumb'] = array(
            'Dashboard'
        );
        $header['subheader_message'] = 'Welcome to the Zenfile Client Portal';
        $this->session->unset_userdata('current_case_id');
        $this->session->unset_userdata('current_case_number');
        $this->session->unset_userdata('current_case_url');
        $this->session->unset_userdata('current_case_type');
        $fa_id = $this->session->userdata('fa_user_id');

        if($this->input->post('search')){
            $params = $this->input->post('search');
        }else{
            $params = '';
        }

        $data['active_cases'] = $this->cases->fa_active_cases($fa_id, $params);
        $data['pending_cases'] = "";
        $data['completed_cases'] = $this->cases->fa_completed_cases($fa_id, $params);
        $this->load->view('parts/header', $header);
        $this->load->view('fa/dashboard', $data);
        $this->load->view('parts/footer');
    }

    public function case_fees($case_number) {

        add_assets(array('styles.zenfile.fa.css' => 'styles.zenfile.fa.css'), 'page');

        $this->load->model('customers_model', 'customers');
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model', 'countries');
        $this->load->model('estimates_model');
        $this->load->model('files_model');

        if (
            (is_null($data['case'] =
            $case = $this->cases->fa_case_fees($case_number)) &&
            ($data['case']["common_status"] != "hidden"))
        ){
            redirect('fa');
        }

        if ($_FILES) {
            if (isset($_FILES{'invoice_file'})) {
                $file_type_id = '7';
                $do_upload = 'invoice_file';
            }
            if (isset($_FILES{'fc_file'})) {
                $file_type_id = '6';
                $do_upload = 'fc_file';
            }
            if (isset($_FILES{'doc_requir_file'})) {
                $file_type_id = '23';
                $do_upload = 'doc_requir_file';
            }
            $userpath = '../pm/uploads/' . $case['user_id'];
            if (!is_dir($userpath)) {
                mkdir($userpath, 0775);
            }
            $usercasepath = $userpath . '/' . $case['case_number'];
            if (!is_dir($usercasepath)) {
                mkdir($usercasepath, 0775);
            }
            $pathToUpload = $usercasepath;

            $config['upload_path'] = $pathToUpload;
            $config['allowed_types'] = '*';
            $config['max_size'] = (1024 * 1024 * 100);

            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload($do_upload)) {
                $data['upload_error'] = $this->upload->display_errors();
            } else {
                $data['upload_data'] = $this->upload->data();
                $file_id = $this->files_model->insert_file(array(
                    'case_id' => $case['id'] ,
                    'user_id' => $case['user_id'] ,
                    'filename' => $data['upload_data']['file_name'] ,
                    'location' => 'uploads/' . $case['user_id'] . '/' . $case['case_number'] . '/' . $data['upload_data']['file_name'] ,
                    'filesize' => $data['upload_data']['file_size'] ,
                    'created_at' => date('Y-m-d H:i:s') ,
                    'owner' => 'fa' ,
                    'visible_to_fa' => 1 ,
                    'file_type_id' => $file_type_id
                ));
                $this->files_model->insert_file_link(array(
                    'file_id' => $file_id ,
                    'country_id' => $this->input->post('country_to_load')
                ));

            }
        }
        if ($this->input->post('tab_to_load') == 'invoice') {
            // functionality for saving invoice of FA

            $associate_data = $this->estimates_model->get_cases_associates_data(array(
                'case_id' => $case['id'],
                'associate_id' => $this->session->userdata('fa_user_id') ,
                'country_id' => $this->input->post('country_to_load')
            ));


            if ($associate_data->fa_invoice_status != 'pending-approval') {

                if (!empty($_POST['additional_fee_by_fa'])) {
                    foreach($this->input->post('additional_fee_by_fa') as $key => $value) {

                        if (empty($_POST['additional_fee_by_fa'][$key])) {
                            continue;
                        }

                        $this->estimates_model->insert_case_country_additional_fees_for_invoice(array(
                            'additional_fee_by_fa' => $_POST['additional_fee_by_fa'][$key] ,
                            'additional_fee_description_by_fa' => $_POST{'additional_fee_description_by_fa'}[$key] ,
                            'cases_associates_data_id' => $associate_data->id
                        ));
                    }
                }

                if (!empty($_POST['additional_fee_id'])) {
                    foreach($_POST['additional_fee_id'] as $key => $value) {
                        $this->estimates_model->update_case_country_additional_fees_for_invoice(array(
                            'additional_fee_by_fa' => $_POST['additional_fee_by_fa_update'][$key] ,
                            'additional_fee_description_by_fa' => $_POST['additional_fee_description_by_fa_update'][$key] ,
                            'additional_fee_id' => $_POST['additional_fee_id'][$key]
                        ));
                    }
                }

                // array for updating case_associate_data
                $update_associate = array(
                    'id' => $associate_data->id ,
                    'fa_invoice_official_fee' => $this->input->post('fa_invoice_official_fee') ,
                    'fa_invoice_professional_fee' => $this->input->post('fa_invoice_professional_fee') ,
                    'fa_invoice_sent' => date('Y-m-d H:i:s')
                );

            }

            $data['fa_invoices'] = $this->estimates_model->get_invoices_of_fa($associate_data->id);

            $data['invoice_additional_fees'] = $this->estimates_model->get_case_country_additional_fees_for_invoice(array(
                'cases_associates_data_id' => $associate_data->id
            ));

            // ready to send to PM and close invoice for FA
            $data['error'] = true;

            if ($data['invoice_additional_fees'] && !empty($data['fa_invoices'])) {
                $update_associate['fa_invoice_status'] = 'pending-approval';
                $data['error'] = false;
            }

            $this->estimates_model->update_associates_data($update_associate);
        }

        $header['page_name'] = 'Case ' . $case_number;
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Cases',
            'Edit',
            $case['case_type'],
            $case_number
        );

        $header['subheader_message'] = 'Case ' . $case_number;
        $data['countries'] = $this->cases->fa_case_countries($case['id'], $this->session->userdata('fa_user_id'));
        $this->load->view('parts/header', $header);
        $this->load->view('fa/case_fees', $data);
        $this->load->view('parts/footer');

    }

    public function add_fa_reference_number(){

        $value = $this->input->post('value');
        $country_id = $this->input->post('country_id');
        $case_id = $this->input->post('case_id');
        $this->db->set('reference_number', $value);
        $this->db->where('case_id', $case_id);
        $this->db->where('country_id', $country_id);
        $this->db->where('is_active', '1');
        $this->db->update('cases_associates_data');

    }

    public function document_required() {

        $this->load->model('cases_model', 'cases');
        $this->load->model('customers_model', 'customers');
        $this->load->model('countries_model', 'countries');
        $this->load->model('send_emails_model', 'send_emails');
        $this->load->model('associates_model', 'associates');
        $country_id = $this->input->post('country_id');
        $case_id = $this->input->post('case_id');
        $value = $this->input->post('value');
        $fa_id = $this->input->post('fa_id');
        $files_id = $this->input->post('files_id_arr');
        $fa_note = $this->input->post('fa_note');
        $case_number = $this->input->post('case_number');
        $note = '';
        $this->cases->document_required_tracker($value, $case_id, $country_id);

        $this->associates->make_note_fa_received_fa_request_email($case_id, $country_id);

        if(!empty($files_id)){
            $this->cases->set_fa_files_visible_to_pm($files_id, $fa_id);
        }

        if(!empty($fa_note)){
            $note = $fa_note;
            $this->cases->insert_fa_case_note($fa_id,$fa_note,$case_number);
        }
        $case = $this->cases->get_case($case_id, false, true);
        $country_data = $this->countries->get_country($country_id);
        if($value == '1'){
            $tpl_num = '35';
        }
        else{
            $tpl_num = '41';
        }
        if(!empty($case['manager_id'])){
            $manager = $this->customers->get_managers($case['manager_id']);

            $TEMPLATE = $this->db->get_where("zen_emails_templates", array("id" => $tpl_num))->row_array();
            $TEMPLATE["subject"] = str_replace("%CASE_APPLICATION_NUMBER%", $case["application_number"], $TEMPLATE["subject"]);
            $TEMPLATE["content"] = str_replace("%CASE_APPLICATION_NUMBER%", $case["application_number"], $TEMPLATE["content"]);
            $TEMPLATE["subject"] = str_replace("%FA_COUNTRY%", $country_data['country'], $TEMPLATE["subject"]);
            $TEMPLATE["content"] = str_replace("%FA_COUNTRY%", $country_data['country'], $TEMPLATE["content"]);
            $TEMPLATE["content"] = str_replace("%CASE_NUMBER%", $case["case_number"], $TEMPLATE["content"]);
            $TEMPLATE["content"] = str_replace("%PM_FIRSTNAME%", $manager["firstname"], $TEMPLATE["content"]);
            $TEMPLATE["content"] = str_replace("%FA_NOTE%", $note, $TEMPLATE["content"]);
            $TEMPLATE["subject"] = str_replace("%CASE_NUMBER%", $case["case_number"], $TEMPLATE["subject"]);

            $from = 'fa'.$case_number. $this->config->item('default_email_box');
            $to = $manager['email'];
            $this->send_emails->send_email(false, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to);
        }
    }

    public function get_country_data($country_id,$case_number) {
        $this->load->model('customers_model', 'customers');
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model', 'countries');
        $this->load->model('associates_model', 'associates');
        $this->load->model('estimates_model');
        $data['case_id'] = $case_number;
        //checking status
        if (
            !(!is_null($data['case'] =
            $case = $this->cases->fa_case_fees($case_number)) &&
            ($data['case']["common_status"] != "hidden"))
        ) {
            die("error");
        }

        //getting current country data
        $data['current_country'] = $this->cases->get_case_country($case['id'],$country_id);

        $data['associate_data'] = $this->estimates_model->get_cases_associates_data(array(
            'case_id' => $case['id'],
            'associate_id' => $this->session->userdata('fa_user_id') ,
            'country_id' => $country_id
        ));

        $data['invoice_additional_fees'] = $this->estimates_model->get_case_country_additional_fees_for_invoice(array(
            'cases_associates_data_id' => $data['associate_data']->id
        ));

        $data['customer'] = $case;
        //getting customer data
        $this->db->where('fr_completed >', '0000-00-00 00:00:00');
        $this->db->where('case_id', $case['id']);
        $this->db->where('country_id', $country_id);
        $query = $this->db->get('cases_tracker');

        if($query->num_rows() || $case["common_status"] == 'completed') {
//                $data['customer'] = $this->customers->get_user($case['user_id']);
            $data['show_info'] = '1';
        } else{
            //$data['current_country']['reference_number'] = 'N/A';
            $data['show_info'] = '0';
        }



        if ($contacts = $this->customers->get_case_contacts($case['id'])) {
            $contacts_arr = array();
            foreach ($contacts as $contact) {
                $contacts_arr[] = $contact['email'];
            }
            //customer contacts data
            $data['customer']['contacts'] = implode('; ', $contacts_arr);
        }
        $fa = $this->associates->get_associate($this->session->userdata('fa_user_id'));
        $data['fa_fee_currency'] = $fa['fee_currency'];
        //getting files
        $data['document_requirements_files'] = $this->cases->get_fa_case_files($case['id'], $country_id, $file_types = array(23));
        $data['document_requirements_show'] = $this->cases->check_document_required_for_country($case['id'], $country_id);
        $data['case_files'] = $this->cases->get_fa_case_files($case['id'], $country_id, $file_types = array(1, 4, 7, 11, 12, 13, 16), false);
        $data['document_files'] = $this->cases->get_fa_case_files($case['id'], $country_id, $file_types = array(2, 10, 14));
        //var_dump($data['document_files']);exit;
        $data['case_files'] = array_merge($data['case_files'], $data['document_files']);
        $data['filing_files'] = $this->cases->get_fa_case_files($case['id'], $country_id, $file_types = array(6));
        $data['filing_confirmation'] = $this->cases->get_filing_confirmation($case['id'], $country_id,$this->session->userdata('fa_user_id'));

        //checking files
        if (isset($data['parent_client_files']) && !is_null($data['client_files'])) {
            $data['client_files'] = array_merge($data['parent_client_files'], $data['client_files']);
        } else if (isset($data['parent_client_files'])) {
            $data['client_files'] = $data['parent_client_files'];
        }

        if (isset($data['parent_document_files']) && !is_null($data['document_files'])) {
            $data['document_files'] = array_merge($data['parent_document_files'], $data['document_files']);
        } else if (isset($data['parent_document_files'])) {
            $data['document_files'] = $data['parent_document_files'];
        }

        //getting manager data
        if (!$manager = $this->customers->get_managers($case['manager_id'])) {
            $manager = array(
                'firstname' => '',
                'lastname' => '',
                'email' => '',
                'phone' => ''
            );
        }

        $manager['fullname'] = implode(' ', array($manager['firstname'], $manager['lastname']));

        $data['manager'] = $manager;
        //getting parent case data
        $parent_case_data = $this->cases->getParentCaseId($case['id']);
        if ($parent_case_data) {
            $data['parent_case'] = $parent_case_data[0]->parent_case_id;
        } else {
            $data['parent_case'] = '';
        }
        $tmp = $this->cases->cases_associates_table($case['id'],$country_id);
        $data['fa_ref_number'] =$tmp['reference_number'];
        $data['country_id'] = $country_id;
        $data['case_countries'] = $this->countries->get_case_countries($case['id'], false);
        if (!empty($data['case_countries'])) {
            foreach ($data['case_countries'] as $key => $country)
            {
                $data['case_countries'][$key]['files'] = $this->cases->get_case_files_with_country_array($case['id'], $file_types = array(6), $country['id']);
            }
        }

        add_assets(array(
            site_url('countries/json_static_countries') => 'application_form.zenfile.js',
        ), 'raw');

        $data['is_passed_country'] = $this->estimates_model->is_deadline_passed_for_country($case['id'] , $country_id);

        $data['fa_invoices'] = $this->estimates_model->get_invoices_of_fa($data['associate_data']->id);

        $data['object'] = $this;

        ////////////
        $this->load->view('/fa/country_data',$data);
        return;


        $result = $query->row_array();
        $data['ref_number'] = $result['reference_number'];
        $data['fa_ref_number'] = $result['fa_reference_number'];

        echo json_encode($data);
    }

    function get_invoices_table($associate_data_id , $disabled = false) {
        $data['disabled'] = $disabled;
        $this->load->model('estimates_model');

        $data['fa_invoices'] = $this->estimates_model->get_invoices_of_fa($associate_data_id);
        $this->load->view('fa/invoices_table' , $data);
    }

    function download_invoice() {
        $this->load->model('estimates_model');
        $this->load->helper('download');
        $invoice_id = intval($this->uri->segment(3));
        $associate_id = $this->session->userdata('fa_user_id');

        $file = $this->estimates_model->get_invoice_of_associate($invoice_id , $associate_id);

        $data = file_get_contents('../pm/' . $file->location); // Read the file's contents

        force_download($file->filename , $data);

    }

    public function document_requirements(){
        $this->load->model('cases_model', 'cases');
        $document_data = array(
            'file_id' => $this->input->post('file_id'),
            'fa_id' => $this->input->post('fa_id')
        );
        $filing_deadline = $this->input->post('file_filing_deadline');
        $hardcopy = $this->input->post('hardcopy');
        $notarization = $this->input->post('notarization');
        $legalization = $this->input->post('legalization');
        $final_deadline = $this->input->post('final_deadline');
        $legalization_by = $this->input->post('legalization_by');
        $filing_fee = $this->input->post('filing_fee');
        $currency = $this->input->post('currency');
        if(!empty($filing_deadline)){
            $document_data['file_filing_deadline'] = date('Y-m-d', strtotime($filing_deadline));
        }
        if(!empty($hardcopy)){
            $document_data['hardcopy'] = $hardcopy;
        }
        if(!empty($notarization)){
            $document_data['notarization'] = $notarization;
        }
        if(!empty($legalization)){
            $document_data['legalization'] = $legalization;
        }
        if(!empty($final_deadline)){
            $document_data['final_deadline'] = date('Y-m-d', strtotime($final_deadline));
        }
        if(isset($legalization_by)){
            $document_data['legalization_by'] = $legalization_by;
        }
        if(!empty($filing_fee)){
            $document_data['filing_fee'] = $filing_fee;
        }
        if(!empty($currency)){
            $document_data['currency'] = $currency;
        }

        $this->cases->save_document_requirements($document_data);
    }
    public function remove_file(){
        exit;
        $file_id = $this->input->post('file_id');
        $this->db->where('file_id', $file_id);
        $this->db->delete('cases_files_data');
        $this->db->where('id', $file_id);
        $this->db->delete('cases_files');

    }

    public function filing_confirmation() {
        $this->load->model('cases_model', 'cases');
        $data = array(
            'fa_id' => $this->input->post('fa_id')
        );

        $doc_pending = $this->input->post('doc_pending');
        $case_id = $this->input->post('case_id');
        $country_id = $this->input->post('country_id');

        $examenation = $this->input->post('examenation');
        $fc_filing_date = $this->input->post('fc_filing_date');
        $fc_application_number = $this->input->post('fc_application_number');
        if(!empty($doc_pending)){
            $data['doc_pending'] = $doc_pending;
        }
        if(!empty($examenation)){
            $data['examenation'] = date('Y-m-d', strtotime($examenation));
        }
        if(!empty($fc_filing_date)){
            $data['filing_date'] = date('Y-m-d', strtotime($fc_filing_date));
        }
        if(!empty($fc_application_number)){
            $data['application_number'] = $fc_application_number;
        }

        $this->cases->update_tracker_additional_data_id($data,$case_id,$country_id);
        $this->cases->update_tracker_filing_date($fc_filing_date, $case_id, $country_id);
    }

    function remove_invoice() {
        $this->load->model('estimates_model');
        $this->load->helper('download');
        $invoice_id = intval($this->input->post('invoice_id'));
        $associate_id = $this->session->userdata('fa_user_id');
        $file = $this->estimates_model->get_invoice_of_associate($invoice_id , $associate_id);

        if ($file) {
            $delete = $this->estimates_model->delete_invoice_link($invoice_id);
        }
    }
}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */