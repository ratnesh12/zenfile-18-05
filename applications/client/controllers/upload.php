<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Upload extends CI_Controller
{
    public function index() {
        $this->load->view("upload_form/upload");
    }

    function test() {
        $this->load->view("upload/test");
    }

    public function do_upload()
    {
        $this->load->model('customers_model', 'customers');
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model', 'countries');
        $this->load->model('estimates_model');
        $this->load->model('files_model');
        $case_number = $this->uri->segment(3);
        if (
            (is_null($data['case'] =
            $case = $this->cases->fa_case_fees($case_number)) &&
                ($data['case']["common_status"] != "hidden"))
        ){
            redirect('fa');
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

        if ( ! $this->upload->do_upload()) {
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
                'file_type_id' => 7
            ));

            $this->files_model->insert_file_link(array(
                'file_id' => $file_id ,
                'country_id' => $this->input->post('country_id')
            ));

            $insert_invoice = array(
                'case_associate_data_id' => $this->input->post('case_associate_data_id') ,
                'file_id' => $file_id ,
                'file_sent' => date('Y-m-d H:i:s') ,
            );

            $this->estimates_model->insert_invoice_link($insert_invoice);
        }
    }

}