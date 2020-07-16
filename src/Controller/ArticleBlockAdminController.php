<?php

namespace App\Controller;

use App\Application\Sonata\BlockBundle\Block\AbstractArticleBlockService;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;

class ArticleBlockAdminController extends Controller
{
    private $blocks;

    public function __construct(iterable $blocks)
    {
        $this->blocks = $blocks;
    }


    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        $article = null;
        if ($request->get('article')) {
            $article = $request->get('article');
        }

        if (!$request->get('type') && $request->isMethod('get')) {
            $types = [];

            foreach ($this->blocks as $key => $value) {
                $types[] = [
                    'name' => $value->getName(),
                    'type' => get_class($value)
                ];
            }

            return $this->renderWithExtraParams('Admin/select_block.html.twig', [
                'types' => $types,
                'article' => $article,
                'action' => 'create',
            ]);
        }

        return parent::createAction();
    }
}
