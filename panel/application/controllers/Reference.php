<?php


class Reference extends CI_Controller
{
    public $viewFolder = "";

    public function __construct()
    {
        parent::__construct();
        $this->viewFolder = "reference_v";
        $this->load->model("reference_model");

        if(!get_active_user())
            redirect(base_url("login"));
    }

    public function index()
    {
        $viewData = new stdClass();

        //Tablodan verilerin getirilmesi...
        $items = $this->reference_model->get_all(
            array(), "rank ASC"
        );

        //View'e gönderilecek değişkenlerin set edilmesi...
        $viewData-> viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "list";
        $viewData->items = $items;

        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    //yeni ürün sayfasına gitmek
    public function new_reference()
    {
        $viewData = new stdClass();

        $viewData-> viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "add";

        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }
    
    //yeni referans eklenmesi
    public function save(){
      $this->load->library("form_validation");

      $this->form_validation->set_rules("title", "Başlık", "required|trim");
      $this->form_validation->set_rules("description", "Açıklama", "trim");
      $this->form_validation->set_message(
          array(
              "required" => "<strong>{field}</strong> alanı zorunludur."
          )
      );
      $validate = $this->form_validation->run();

      if ($validate) {

          $file_name = rand().rand().converToSEO(pathinfo($_FILES["img_url"]["name"], PATHINFO_FILENAME)) . "." . pathinfo($_FILES["img_url"]["name"], PATHINFO_EXTENSION);

          $image555x343 = upload_image($_FILES["img_url"]["tmp_name"], "uploads/$this->viewFolder/", 555,343, $file_name);
          $image350x217 = upload_image($_FILES["img_url"]["tmp_name"], "uploads/$this->viewFolder/", 350,217, $file_name);
          $image70x70 = upload_image($_FILES["img_url"]["tmp_name"], "uploads/$this->viewFolder/", 70,70, $file_name);


          if ($image555x343 && $image350x217 && $image70x70) {

              $insert = $this->reference_model->add(
                  array(
                      "url" => converToSEO($this->input->post("title")),
                      "title" => $this->input->post("title"),
                      "description" => $this->input->post("description"),
                      "img_url" => $file_name,
                      "rank" => 0,
                      "isActive" => true,
                      "createdAt" => date("Y-m-d H:i:s")
                  )

              );

              if ($insert) {
                  $alert = array(
                      "title" => "Tebrikler",
                      "text" => "İşleminiz başarılı bir şekilde gerçekleştirildi.",
                      "type" => "success"
                  );
              } else {
                  $alert = array(
                      "title" => "İşlem başarısız",
                      "text" => "Lütfen zorunlu olan alanları doldurunuz!",
                      "type" => "error"
                  );
              }

              $this->session->set_flashdata("alert", $alert);
              redirect(base_url("reference"));

          } else {
              $alert = array(
                  "title" => "Opppss",
                  "text" => "Resim yüklenme esnasında bir problem oluştu.",
                  "type" => "error"
              );
              $this->session->set_flashdata("alert", $alert);
              redirect(base_url("reference/new_reference"));
          }
      }
      else{
          $viewData = new stdClass();

          $viewData-> viewFolder = $this->viewFolder;
          $viewData->subViewFolder = "add";
          $viewData->form_error = "true";

          $alert = array(
              "title"   => "İşlem başarısız",
              "text"    => "Lütfen zorunlu olan alanları doldurunuz!",
              "type"    => "error"
          );

          $this->session->set_flashdata("alert", $alert);
          $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
          unset($_SESSION['alert']);
      }
    }

    //düzenlenecek sayfaya gitmek
    public function update_reference($id){
        $viewData = new stdClass();

        $item = $this->reference_model->get(
            array(
                "id" => $id
            )
        );

        $viewData-> viewFolder = $this->viewFolder;
        $viewData->subViewFolder = "update";
        $viewData->item = $item;

        $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
    }

    public function update($id)
    {
        $this->load->library("form_validation");

        $this->form_validation->set_rules("title", "Başlık", "required|trim");
        $this->form_validation->set_rules("description", "Açıklama", "trim");

        $this->form_validation->set_message(
            array(
                "required" => "<strong>{field}</strong> alanı doldurulmalıdır."
            )
        );

        $validate = $this->form_validation->run();

       if($validate){
           if ($_FILES["img_url"]["name"] !== "") {
               $select_img = $this->reference_model->get(
                   array(
                       "id" => $id
                   )
               );
               if ($select_img){
                   $paths = array(
                       $path1 = "uploads/$this->viewFolder/555x343/$select_img->img_url",
                       $path2 = "uploads/$this->viewFolder/70x70/$select_img->img_url",
                       $path3 = "uploads/$this->viewFolder/350x217/$select_img->img_url"
                   );

                   foreach ($paths as $path)
                       $delete_img = unlink($path);

                   if (!$delete_img){
                       $alert = array(
                           "title"   => "İşlem başarısız",
                           "text"    => "Fotoğraf silinirken bir sorunla karşılaşıldı.",
                           "type"    => "error"
                       );

                       $this->session->set_flashdata("alert", $alert);
                       redirect(base_url("reference/update_reference/$id"));
                   }else{
                       $file_name = rand().rand().converToSEO(pathinfo($_FILES["img_url"]["name"], PATHINFO_FILENAME)) . "." . pathinfo($_FILES["img_url"]["name"], PATHINFO_EXTENSION);

                       $image555x343 = upload_image($_FILES["img_url"]["tmp_name"], "uploads/$this->viewFolder/", 555,343, $file_name);
                       $image350x217 = upload_image($_FILES["img_url"]["tmp_name"], "uploads/$this->viewFolder/", 350,217, $file_name);
                       $image70x70 = upload_image($_FILES["img_url"]["tmp_name"], "uploads/$this->viewFolder/", 70,70, $file_name);

                       if ($image555x343 && $image350x217 && $image70x70){
                           $data = array(
                               "url" => converToSEO($this->input->post("title")),
                               "title" => $this->input->post("title"),
                               "description" => $this->input->post("description"),
                               "img_url" => $file_name,
                           );
                       }else{
                           $alert = array(
                               "title" => "Opppss",
                               "text" => "Resim yüklenme esnasında bir problem oluştu.",
                               "type" => "error"
                           );
                           $this->session->set_flashdata("alert", $alert);
                           redirect(base_url("reference/update_reference/$id"));
                       }
                   }
               }else{
                   $alert = array(
                       "title" => "İşlem başarısız",
                       "text" => "Lütfen zorunlu olan alanları doldurunuz!",
                       "type" => "error"
                   );

                   $this->session->set_flashdata("alert", $alert);
                   redirect(base_url("reference"));
               }
           }else{
               $data = array(
                   "url" => converToSEO($this->input->post("title")),
                   "title" => $this->input->post("title"),
                   "description" => $this->input->post("description"),
               );
           }
           $update = $this->reference_model->update(array("id" => $id), $data);

           if ($update){
               $alert = array(
                   "title" => "Tebrikler",
                   "text" => "İşleminiz başarılı bir şekilde gerçekleştirildi.",
                   "type" => "success"
               );
           }else{
               $alert = array(
                   "title" => "İşlem başarısız",
                   "text" => "Lütfen zorunlu olan alanları doldurunuz!",
                   "type" => "error"
               );
           }
           $this->session->set_flashdata("alert", $alert);
           redirect(base_url("reference"));
       }else{
           $viewData = new stdClass();

           $viewData-> viewFolder = $this->viewFolder;
           $viewData->subViewFolder = "update";
           $viewData->form_error = "true";

           $viewData->item = $this->reference_model->get(
               array(
                   "id" => $id
               )
           );

           $alert = array(
               "title"   => "İşlem başarısız",
               "text"    => "Lütfen zorunlu olan alanları doldurunuz!",
               "type"    => "error"
           );

           $this->session->set_flashdata("alert", $alert);
           $this->load->view("{$viewData->viewFolder}/{$viewData->subViewFolder}/index", $viewData);
           unset($_SESSION['alert']);
       }
    }

    public function delete($id){

        $select_img = $this->reference_model->get(
            array(
                "id" => $id
            )
        );
        if($select_img){
            $paths = array(
                $path1 = "uploads/$this->viewFolder/555x343/$select_img->img_url",
                $path2 = "uploads/$this->viewFolder/70x70/$select_img->img_url",
                $path3 = "uploads/$this->viewFolder/350x217/$select_img->img_url"
            );

            foreach ($paths as $path)
                $delete_img = unlink($path);

            if($delete_img){
                $delete = $this->reference_model->delete(
                    array(
                        "id" => $id
                    )
                );
                if ($delete){
                    $alert = array(
                        "title"   => "Tebrikler",
                        "text"    => "İşleminiz başarılı bir şekilde gerçekleştirildi.",
                        "type"    => "success"
                    );
                }
                else{
                    $alert = array(
                        "title"   => "İşlem başarısız",
                        "text"    => "Lütfen zorunlu olan alanları doldurunuz!",
                        "type"    => "error"
                    );
                }

                $this->session->set_flashdata("alert", $alert);
                redirect(base_url("reference"));
            }
            else{
                $alert = array(
                    "title"   => "İşlem başarısız",
                    "text"    => "Silinecek bir resim yok veya veri yolu hatalı",
                    "type"    => "error"
                );
            }

            $this->session->set_flashdata("alert", $alert);
            redirect(base_url("reference"));
        }
        else{
            $alert = array(
                "title"   => "İşlem başarısız",
                "text"    => "Resim bulunamadı",
                "type"    => "error"
            );
        }

        $this->session->set_flashdata("alert", $alert);
        redirect(base_url("reference"));
    }

    public function isActiveSetter($id){

        if($id){

            $isActive = ($this->input->post("data") === "true") ? 1 : 0;

            $this->reference_model->update(
                array(
                    "id" => $id,
                ),
                array(
                    "isActive" => $isActive
                )
            );
        }

    }

    public function rankSetter(){
        $data = $this->input->post("data");

        parse_str($data, $order);
        $items = $order["ord"];

        foreach ($items as $rank => $id){
            $this->reference_model->update(
                array(
                    "id"        =>  $id,
                    "rank !="   =>  $rank
                ),
                array(
                    "rank" => $rank
                )
            );
        }
    }

}
