<?php
$unicode = '';
for($i = 0; $i < ( 0x9fa5 - 0x4e00 ); $i++){
    $unicode .= '\\u' . dechex(0x4e00 + $i);
}
file_put_contents('hanzi.txt', json_decode('{"test": "'.$unicode.'"}', true)['test']);