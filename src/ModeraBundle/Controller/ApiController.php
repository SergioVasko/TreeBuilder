<?php

namespace ModeraBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends Controller
{
    /**
     * Build a tree from an input file and return it in json
     *
     * @param string $input Input csv file
     * @return JsonResponse Transformed input
     *
     * @Route("/api/tree/{input}", name="tree")
     */
    public function indexAction($input)
    {
        $file = __DIR__.'/../Resources/input/'.$input.'.csv';
        if (!is_readable($file)) {
            throw $this->createNotFoundException('Unable to find file.');
        }

        $content = file($file);
        $num = count($content);
        $rows = array_map('str_getcsv', $content, array_fill(0, $num, '|'));

        foreach($rows as $row) {
            $nodes_name[] = strtolower($row[2]);
        }

        array_multisort($nodes_name, SORT_STRING, $rows);
        $list = array_map(
            'array_combine',
            array_fill(0, $num, array('node_id', 'parent_id', 'node_name')),
            $rows
        );
        $tree = $this->buildTree($list, 'parent_id', 'node_id');

        return new JsonResponse(array('tree' => $tree));
    }

    /**
     * Build a tree
     *
     * @param array       $list Plain array
     * @param string      $pidKey Parent's id key
     * @param string|null $idKey Entity's id key
     *
     * @return array
     */
    private function buildTree($list, $pidKey, $idKey = null)
    {
        $grouped = array();
        foreach ($list as $sub) {
            $grouped[$sub[$pidKey]][] = $sub;
        }

        $fnBuilder = function($siblings) use (&$fnBuilder, $grouped, $idKey)
        {
            foreach ($siblings as $k => $sibling) {
                $id = $sibling[$idKey];
                if(isset($grouped[$id])) {
                    $sibling['children'] = $fnBuilder($grouped[$id]);
                }
                $siblings[$k] = $sibling;
            }

            return $siblings;
        };

        $tree = $fnBuilder($grouped[0]);

        return $tree;
    }
}
