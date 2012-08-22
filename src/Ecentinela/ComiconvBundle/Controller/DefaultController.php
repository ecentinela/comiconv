<?php

namespace Ecentinela\ComiconvBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\File\File,
    Symfony\Component\HttpKernel\Exception\HttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Ecentinela\ComiconvBundle\Entity\Conversion;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        return $this->redirect(
            $this->generateUrl('input', array(
                '_locale' => substr(
                    $request->getPreferredLanguage(),
                    0,
                    2
                )
            ))
        );
    }

    /**
     * @Route("/contact", name="contact")
     * @Method("POST")
     */
    public function contactAction(Request $request)
    {
        $message = \Swift_Message::newInstance()
                                 ->setSubject('Comiconv - Contact')
                                 ->setFrom(
                                    'no-reply@comiconv.com'
                                 )
                                 ->setTo(
                                    $this->container->getParameter('contact_email')
                                 )
                                 ->setBody(
                                    $request->request->get('email').
                                    "\n\n".
                                    $request->request->get('text')
                                 );

        if ($this->get('mailer')->send($message)) {
            return new Response('sent');
        }

        throw new HttpException(500, 'Can not send email');
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
    public function outputAction(Request $request, $hash)
    {
        // get the entity manager
        $em = $this->getDoctrine()
                   ->getEntityManager();

        // get the conversion
        $conversion = $em->getRepository('EcentinelaComiconvBundle:Conversion')
                         ->findOneBy(array(
                            'hash' => $hash
                         ));

        // invalid conversion status, redirect to input
        if (!$conversion || $conversion->getStatus() == 'uploading' || $conversion->getStatus() == 'removed') {
            return $this->redirect(
                $this->generateUrl('input')
            );
        }

        // update conversion email if parameter in request
        if ($request->getMethod() == 'POST' && $request->request->has('email')) {
            $conversion->setEmail(
                $request->request->get('email')
            );

            $em->flush();
        }

        // get the file (if exists)
        $path = $this->get('kernel')->getRootDir().'/../files/output/'.$conversion->getHash().'.'.$conversion->getFormat();
        $file = file_exists($path) ? new File($path) : null;

        // render template
        return array(
            'conversion' => $conversion,
            'file' => $file
        );
    }

    /**
     * @Route("/upload", name="upload", options={ "expose" = true })
     */
    public function uploadAction(Request $request)
    {
        // get uploaded file
        $file = $request->files->get('file');

        // upload is ok
        if ($file->isValid()) {
            if (preg_match('/(pdf|cbz|zip|jpg|jpeg)/', $file->guessExtension())) {
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
                } else {
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
     * @Route("/download/{hash}", name="download")
     */
    public function downloadAction($hash)
    {
        // get the entity manager
        $em = $this->getDoctrine()
                   ->getEntityManager();

        // get the conversion
        $conversion = $em->getRepository('EcentinelaComiconvBundle:Conversion')
                         ->findOneBy(array(
                            'hash' => $hash
                         ));

        if (!$conversion) {
            throw $this->createNotFoundException('Unable to find conversion.');
        }

        $file = new File(
            $this->get('kernel')->getRootDir().'/../files/output/'.$conversion->getHash().'.'.$conversion->getFormat()
        );

        return new Response(
            file_get_contents(
                $file->getPathname()
            ),
            200,
            array(
                'Content-Type' => $file->getMimeType(),
                'Content-Disposition' => 'attachment; filename="'.$hash.'.'.$file->getExtension().'"'
            )
        );
    }
}
