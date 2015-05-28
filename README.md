Tree builder
--------------
The Bundle take the name of a data file as a parameter and returns the result as json tree.
The text file is a CSV where each line is formatted as follows:
`node_id|parent_id|node_name`

The `web\plainphp\build-tree.php` is a simple php code that converts the data source file in json tree.
The `web\plainphp\extjs-tree.html` builds a tree from input data in the format json with a ExtJS-tree component.
