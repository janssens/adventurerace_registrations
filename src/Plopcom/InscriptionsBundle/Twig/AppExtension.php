<?php
namespace Plopcom\InscriptionsBundle\Twig;

use Plopcom\InscriptionsBundle\Entity\Inscription;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('human_status', array($this, 'humanStatus')),
            new \Twig_SimpleFilter('human_payement_status', array($this, 'humanPaymentStatus')),
            new \Twig_SimpleFilter('ext', array($this, 'ext')),
            new \Twig_SimpleFilter('ext_type', array($this, 'ext_type')),
        );
    }

    public function humanStatus($integer)
    {
        $return = '';
        switch ($integer){
            case Inscription::STATUS_UNCHECKED:
                $return = "<span class=\"label label-default\">non vérifié</span>";
                break;
            case Inscription::STATUS_UNVALID:
                $return = "<span class=\"label label-danger\">non valide</span>";
                break;
            case Inscription::STATUS_VALID:
                $return = "<span class=\"label label-success\">validé</span>";
                break;
            default:
                $return = '<span class="label label-default">indéfini</span>';
        }
        return $return;
    }

    public function humanPaymentStatus($integer)
    {
        $return = '';
        switch ($integer){
            case Inscription::PAYEMENT_STATUS_FAILED:
                $return = "<span class=\"label label-danger\">payement échoué</span>";
                break;
            case Inscription::PAYEMENT_STATUS_NOT_PAYED:
                $return = "<span class=\"label label-info\">non payé</span>";
                break;
            case Inscription::PAYEMENT_STATUS_PAYED:
                $return = "<span class=\"label label-success\">payé</span>";
                break;
            case Inscription::PAYEMENT_STATUS_WAITING:
                $return = "<span class=\"label label-success\">En attente retour</span>";
                break;
            default:
                $return = '<span class="label label-default">indéfini</span>';
        }
        return $return;
    }

    public function ext($filepath){
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        return $ext;
    }

    public function ext_type($ext){
        switch($ext){
            case 'pdf':
                $return = 'pdf';
                break;
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
                $return = 'image';
                break;
            default :
                $return = '';
        }
        return $return;
    }

    public function getName()
    {
        return 'app_extension';
    }
}