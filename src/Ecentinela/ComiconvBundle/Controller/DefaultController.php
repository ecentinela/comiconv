<?php

namespace Ecentinela\ComiconvBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Ecentinela\ComiconvBundle\Entity\Conversion;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->redirect(
            $this->generateUrl('input', array(
                '_locale' => substr(
                    $this->getRequest()->getPreferredLanguage(),
                    0,
                    2
                )
            ))
        );
    }

    /**
     * @Route("/{_locale}", name="input", requirements={ "_locale" = "en|es" })
     * @Template()
     */
    public function inputAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}", name="output", requirements={ "_locale" = "en|es" })
     * @Template()
     */
    public function outputAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}/about", name="about", requirements={ "_locale" = "en|es" })
     * @Template()
     */
    public function aboutAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}/contact", name="contact", requirements={ "_locale" = "en|es" })
     * @Template()
     */
    public function contactAction()
    {
        return array();
    }
}
