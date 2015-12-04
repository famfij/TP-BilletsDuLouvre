<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 15/11/2015
 * Time: 21:44
 */

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CountriesController extends FOSRestController
{
    /**
     * @View()
     * @Get("/api/v1/countries.{_format}", requirements={"_format"="json, xml"}, name="get_countries", defaults={"_format"="json"})
     * @ApiDoc(description="get the list of all existing country names")
     */
    public function getCountriesAction()
    {
        $countries = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Country')
            ->findAllOrderedByName();

        return $countries;
    }
}