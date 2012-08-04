<?php

namespace Ecentinela\ComiconvBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @Route("/{_locale}/upload", name="upload", requirements={ "_locale" = "en|es" }, options={ "expose" = true })
     */
    public function uploadAction()
    {
        // get the request
        $request = $this->getRequest();

        // get uploaded file
        $file = $request->files->get('file');

        // upload is ok
        if ($file->isValid()) {
            if (preg_match('/(pdf|cbz|zip|jpg)/', $file->guessExtension()))
            {
                // get the entity manager and the repository
                $em = $this->getDoctrine()->getEntityManager();
                $repository = $em->getRepository('EcentinelaComiconvBundle:Conversion');

                // find the conversion with the given hash
                $conversion = $repository->findOneBy(array(
                    'hash' => $request->request->get('hash')
                ));

                // if no conversion found, create a new one
                if (!$conversion) {
                    $conversion = new Conversion();

                    $conversion->setRetries(0);

                    $conversion->setHash(
                        $request->request->get('hash')
                    );

                    $conversion->setFormat(
                        $request->request->get('format')
                    );

                    $conversion->setTotalFiles(
                        $request->request->get('total')
                    );

                    $conversion->setStatus('uploading');

                    $path = $this->get('kernel')->getRootDir().'/../files/input/'.$conversion->getHash().'/';

                    if (file_exists($path)) {
                        throw new HttpException(409, 'Invalid hash');
                    }
                }
                else {
                    $path = $this->get('kernel')->getRootDir().'/../files/input/'.$conversion->getHash().'/';
                }

                // increment uploaded files
                $conversion->setUploadedFiles(
                    $conversion->getUploadedFiles() + 1
                );

                // if all uploads are complete, mark the conversion as uploaded
                if ($conversion->getUploadedFiles() == $conversion->getTotalFiles()) {
                    $conversion->setStatus('uploaded');
                }

                // move uploaded file
                $file->move(
                    $path,
                    $conversion->getUploadedFiles().'.'.$file->guessExtension()
                );

                // save conversion
                $em->persist($conversion);
                $em->flush();

                return new Response('Upload OK', 200);
            }

            throw new HttpException(500, 'Invalid file type: ' . $file->guessExtension());
        }

        throw new HttpException(500, 'Upload file');
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
     * @Route("/{_locale}/{hash}", name="output", requirements={ "_locale" = "en|es" }, options={ "expose" = true })
     * @Template()
     */
    public function outputAction($hash)
    {
        // get the conversion
        $conversion = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('EcentinelaComiconvBundle:Conversion')
                           ->findOneBy(array(
                            'hash' => $hash
                           ));

        // invalid conversion status, redirect to input
        if (!$conversion || $conversion->getStatus() == 'uploading' || $conversion->getStatus() == 'removed')
        {
            return $this->redirect(
                $this->generateUrl('input')
            );
        }

        // render template
        return array(
            'conversion' => $conversion
        );
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
