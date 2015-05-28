<?php

if (false === ($content = file('input.csv'))) {
    exit('Error opening file');
}

$num = count($content);
$delimiter = array_fill(0, $num, '|');
$rows = array_map('str_getcsv', $content, $delimiter);

foreach($rows as $row) {
	$nodes_name[] = strtolower($row[2]);
}
array_multisort($nodes_name, SORT_STRING, $rows);

$list = array_map(
    'array_combine',
    array_fill(0, $num, array('node_id', 'parent_id', 'node_name')),
    $rows
);

$tree = buildTree($list, 'parent_id', 'node_id');

header('Content-type: application/json');
print(json_encode($tree));


/**
 * Build tree
 *
 * @param array       $list Plain array
 * @param string      $pidKey Parent's id key
 * @param string|null $idKey Entity's id key
 *
 * @return array
 */
function buildTree($list, $pidKey, $idKey = null)
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