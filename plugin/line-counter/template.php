<?php

$ch = curl_init();

$url = 'http://localhost/lines/index.php?';
//Edit your options as needed
$url .= http_build_query(array(
            'dir' => dirname(__DIR__),
            'options' => array(
                'ignoreFolders' => explode(', ', '.svn, backups, db, nbproject, assets, commands, data, extensions, messages, runtime, tests'),
                'ignoreFiles' => explode(', ', 'index-test.php, jquery-1.5.js, jquery-1.5.min.js, yiic.php'),
                'extensions' => explode(', ', 'php, js'),
                'whitespace' => false, //Whether to count blank lines
                'comments' => false, //Wheter to count comments
            ),
        ));

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$output = curl_exec($ch);
echo $output;

curl_close($ch);
?>