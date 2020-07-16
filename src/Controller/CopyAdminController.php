<?php

namespace App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CopyAdminController extends CRUDController
{
    /**
     * @param $id
     */
    public function cloneAction($id, Request $request)
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        try {
            $clonedObject = clone $object;

            $this->admin->create($clonedObject);

            $this->addFlash('sonata_flash_success', 'Entry has been duplicated: <a href="' . $this->admin->generateUrl('edit', array('id' => $clonedObject->getId())) . '">Edit</a>');
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', 'Entry could not be duplicated: ' . $e->getMessage());
        }

        if (($referer = $request->server->get('HTTP_REFERER'))) {
            $redirectTarget = $referer;
        } else {
            $redirectTarget = $this->admin->generateUrl('list');
        }

        return new RedirectResponse($redirectTarget);
    }
}
