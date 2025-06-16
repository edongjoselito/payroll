<?php
class Rate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Project_model');
    }

    public function index()
    {
        $data['rates'] = $this->Project_model->get_all_rates();
        $this->load->view('rate_view', $data);
    }

    public function store()
    {
        $data = [
            'rateType' => $this->input->post('rateType'),
            'rateAmount' => $this->input->post('rateAmount')
        ];

        if ($this->Project_model->insert_rate($data)) {
            $this->session->set_flashdata('success', 'Rate successfully added.');
        } else {
            $this->session->set_flashdata('error', 'Failed to add rate.');
        }

        redirect('rate');
    }

    public function update()
    {
        $rateID = $this->input->post('rateID');
        $data = [
            'rateType' => $this->input->post('rateType'),
            'rateAmount' => $this->input->post('rateAmount')
        ];

        if ($this->Project_model->update_rate($rateID, $data)) {
            $this->session->set_flashdata('success', 'Rate successfully updated.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update rate.');
        }

        redirect('rate');
    }

    public function delete($rateID)
    {
        if ($this->Project_model->delete_rate($rateID)) {
            $this->session->set_flashdata('success', 'Rate deleted.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete rate.');
        }

        redirect('rate');
    }
}
