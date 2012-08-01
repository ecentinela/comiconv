<?php

namespace Ecentinela\ComiconvBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Response;

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
     * @Route("/{_locale}/upload", name="upload", requirements={ "_locale" = "en|es" })
     */
    public function uploadAction()
    {
        exit;
        // get the request
        $request = $this->getRequest();

        // get params
        $params = $request->request;

        // get uploaded file
        $file = $request->files->get('file');

        // upload is ok
        if ($file->isValid()) {
            if (preg_match('/(png|cbz|jpg)/', $file->getMimeType()))
            {
                // get the entity manager and the repository
                $em = $this->getDoctrine()->getEntityManager();
                $repository = $em->getRepository('EcentinelaComiconvBundle:Conversion');

                // find the conversion with the given hash
                $conversion = $repository->findOneBy(array(
                    'hash' => $params->get('hash')
                ));

                // if no conversion found, create a new one
                if (!$conversion) {
                    $conversion = new Conversion();

                    $conversion->setTotalFiles(
                        $params->get('total')
                    );

                    $conversion->setStatus('uploading');
                }

                // increment uploaded files
                $conversion->setUploadedFiles(
                    $conversion->getUploadedFiles() + 1
                );

                // if all uploads are complete, mark the conversion as uploaded
                if ($conversion->getUploadedFiles() == $conversion->getTotalFiles()) {
                    $conversion->setStatus('uploaded');
                }

                // save conversion
                $em->persist($conversion);
                $em->flush();
            }
        }

        return new Response('', 500);
    }

    /**
     * @Route("/{_locale}", name="input", requirements={ "_locale" = "en|es" })
     * @Template()
     */
    public function inputAction()
    {
        return array(
            'hash' => base_convert(sha1(uniqid(mt_rand(), TRUE)), 16, 36),
            'maxFileSize' => trim(ini_get('upload_max_filesize'))
        );
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
