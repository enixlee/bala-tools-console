<?php
/**
 * Created by PhpStorm.
 * User: peng
 * Date: 15-10-14
 * Time: 下午5:22
 */

/*for($i=0;$i<3;$i++){
    if($i==1){
        continue;
    }
    echo $i."\n";
}*/
//过滤数组中的空值
/*$array=['1','2','false','','null','0'];
$result = array_filter( $array, 'strlen' );
var_dump($result);*/

/*$file='0asset_png/';
if (preg_match_all('/^([\w\/]*)?$/', $file)) {
    echo '没有'."\n";
} else {
    echo "有"."\n";
}*/

/*$a=1;
$b=2;
$c=3;
if($a==1 && $b==2 && $c==3){
    echo 1;
}else{
    echo 2;
}*/
/*if(0 == false){
    echo 1;
}*/

/*function arr_foreach ($arr)
{
    static $tmp=array();
    if (!is_array ($arr))
    {
        return false;
    }
    foreach ($arr as $val )
    {
        if (is_array ($val))
        {
            arr_foreach ($val);
        }
        else
        {
            $tmp[]=$val;
        }
    }
    return $tmp;
}*/

/*function array_merge_rec($array) {  // 参数是使用引用传递的
    // 定义一个新的数组
    $new_array = array ();
    // 遍历当前数组的所有元素
    foreach ( $array as $item ) {
        if (is_array ( $item )) {
            // 如果当前数组元素还是数组的话，就递归调用方法进行合并
            array_merge_rec ( $item );
            // 将得到的一维数组和当前新数组合并
            $new_array = array_merge ( $new_array, $item );
        } else {
            // 如果当前元素不是数组，就添加元素到新数组中
            $new_array [] = $item;
        }
    }
    // 修改引用传递进来的数组参数值
    $array = $new_array;
    return $array;
}*/

/*
function array_multiToSingle($array,$clearRepeated=false){

    if(!isset($array)||!is_array($array)||empty($array)){

        return false;

    }

    if(!in_array($clearRepeated,array('true','false',''))){

        return false;

    }

    static $result_array=array();

    foreach($array as $value){

        if(is_array($value)){

            array_multiToSingle($value);

        }else{

            $result_array[]=$value;

        }

    }

    if($clearRepeated){
        $result_array=array_unique($result_array);
    }
    return $result_array;
}

$a = array(1,2=>array(3,4=>array(5,6)),7);
print_r(array_multiToSingle($a,$clearRepeated));*/


/*$lines = [
    'aaa',
    'bbb'
]
$outArrs = [];
for ($i = 4,$i<count($lines);$i++)
{
    $line = $lines[$i];

    $line_seps = explore($line, ' ');

    foreach($line_seps as $line_sep)
    {
        $line_sep = trim($line_sep);
        $outArr;
        if(empty($line_sep))
        {
            continue;
        }

        if($line_sep::start_with('id'))
        {
            //取值 等号分割
            outArr('id') = line_sep[=][1];
		}
    }

{ ["width"] =12 , ["height"] = 24 , ["xadvance"] = 13 },
    //
    $outArrs[] = $outArr;

}*/

$arr=['a'=>'1','b'=>2];
$arr1=['c'=>1,'d'=>3];
$arr2 = $arr + $arr1;
//$arr2 = array_merge_recursive($arr,$arr1);
var_dump($arr2);