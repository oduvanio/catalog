<?php
/**
 * Блок "filters"
 */

namespace itlife\catalog;

use itlife\files\Xlsx;

$ans=array();
$md=Catalog::initMark($ans);
$conf=infra_config();

$args=array(Catalog::nocache($md));
$res=Catalog::cache('filters.php filter list', function ($md) {
	$conf=infra_config();
	$poss=Catalog::getPoss($md['group']);
    
    $ans=array();
    
    $params=array();//параметры

    //ПОСЧИТАЛИ COUNT
    $count=sizeof($poss); //количество позиций
    $main=array(
		'Производитель'=>array('posid'=>'producer','posname'=>'Производитель','mdid'=>'producer'),
		'Цена'=>array('posid'=>'Цена','posname'=>'Цена','mdid'=>'cost')
	);
	
	$main=array_merge($main,$conf['catalog']['filters']);
	// Заголовок блока => array('id'=>'Имя свойства в md', 'name'=>'Имя свойства в pos')
    foreach($poss as &$pos){    
        if($pos['more'])foreach($pos['more'] as $k=>$val){
            if(preg_match("/[:]/", $val))continue;
            if(preg_match("/[:]/", $k))continue;
            if(!Xlsx::isSpecified($val))continue;
            if(!isset($params[$k])) $params[$k]=array('posname'=>$k, 'posid'=>$k, 'mdid'=>$k, 'title'=>$k, 'more'=>true, 'option'=>array(), 'count'=>0, 'filter'=>0, 'search'=>0);
            if(!isset($params[$k]['option'][$val]))$params[$k]['option'][$val]=array('id'=>$val,'title'=>$val, 'count'=>0, 'filter'=>0, 'search'=>0);
            $params[$k]['option'][$val]['count']++;
        }
        foreach($main as $k=>$prop){
            $val=$pos[$prop['posid']];
            if(preg_match("/[:]/", $val))continue;
            if(!Xlsx::isSpecified($val))continue;
            if(!isset($params[$k])) $params[$k]=array('posname'=>$prop['posname'], 'posid'=>$prop['posid'], 'mdid'=>$prop['mdid'],'title'=>$k, 'option'=>array(), 'count'=>0, 'filter'=>0, 'search'=>0);
            if(!isset($params[$k]['option'][$val]))$params[$k]['option'][$val]=array('id'=>$val,'title'=>$pos[$prop['posname']], 'count'=>0, 'filter'=>0, 'search'=>0);
            $params[$k]['option'][$val]['count']++;
			$params[$k]['count']++;
        }
		
    }

    $res=Catalog::search($md);
    $poss=$res['list'];
    $search=sizeof($poss);
    //ПОСЧИТАЛИ NOW
    foreach($params as $k=>$v){
        if($v['more']){
            foreach($poss as &$pos){
                if(!Xlsx::isSpecified($pos['more'][$v['posid']]))continue;    
                $params[$k]['option'][$pos['more'][$v['posid']]]['search']++;
				$params[$k]['search']++;
            }
        }else{
            foreach($poss as &$pos){
                if(!Xlsx::isSpecified($pos[$v['posid']]))continue;
                $params[$k]['option'][$pos[$v['posid']]]['search']++;
				$params[$k]['search']++;
            }
        }
		$params[$k]['nosearch']=sizeof($poss)-$params[$k]['search'];
    }
	
    //ПОСЧИТАЛИ FILTER
    foreach ($params as $k => $v) {
        if ($v['more']) {
            $mymd=$md;
            $mymd['more']=array_diff_key($md['more'], array_flip(array($v['mdid'])));
            $res=Catalog::search($mymd);
            $poss=$res['list'];
            foreach($poss as &$pos){
                if(preg_match("/[:]/", $pos['more'][$v['posid']]))continue;
                if(!Xlsx::isSpecified($pos['more'][$v['posid']]))continue;
                $params[$k]['option'][$pos['more'][$v['posid']]]['filter']++;
				$params[$k]['filter']++;
            }
        } else {
            $mymd=array_diff_key($md, array_flip(array($v['mdid'])));
            $res=Catalog::search($mymd);
            $poss=$res['list'];
            foreach($poss as &$pos){
                if(preg_match("/[:]/", $pos[$v['posid']]))continue;
                if(!Xlsx::isSpecified($pos[$v['posid']]))continue;
                $params[$k]['option'][$pos[$v['posid']]]['filter']++;
				$params[$k]['filter']++;
            }
        }
		$params[$k]['nofilter']=sizeof($poss)-$params[$k]['filter'];
    }
	
    //ДОБАВИЛИ option values
    foreach($params as $k=>$v){
        if ($v['more']) {
            $right=array('more', $v['mdid']);    
            $add='more.';
        }else{
            $right=array($v['mdid']);    
            $add='';
        }
        $showhard=infra_seq_get($md, $right);
        $opt=Catalog::option($params[$k]['option'], $count, $search, $showhard);
        if (!$opt) {
            unset($params[$k]);
        } else {
            $params[$k]['option']=$opt;
        }
    }
	
    
    usort($params,function($p1, $p2){
        if($p1['count']>$p2['count'])return -1;
        if($p1['count']<$p2['count'])return 1;
        return 0;
    });
    

    $ans['params']=$params;
    $ans['count']=$search;
    $ans['template']=array();
    foreach($params as $param){
        $block=array();
        
        if ($param['more']) {
            $right=array('more', $param['mdid']);    
            $add='more.';
        }else{
            $right=array($param['mdid']);    
            $add='';
        }
        $mymd=infra_seq_get($md, $right);
        if (!$mymd) $mymd=array();
        
        
        $paramid=infra_seq_short(array(Catalog::urlencode($param['mdid'])));
        $block['checked']=!!$mymd['yes'];
        
        //if($param['option']['yesall']==$count&&sizeof($param['option']['values'])<2){
        //    continue;
        //}
        if($block['checked']){
            $block['add']=$add.$paramid.'.yes:';
        } else {
            $block['add']=$add.$paramid.'.yes:1';  
        }
        
        $block['title']=$param['title'];
        $block['type']=$param['option']['type'];
        $block['count']=$param['filter'];
        $block['row']=array();
        if($param['option']['noall']){
			
		
            $row=array(
                'title'=>'Не указано',
                'count'=>$param['nofilter']
            );
            $row['checked']=!!$mymd['no'];
            if($row['checked']){
                $row['add']=$add.$paramid.'.no:';
            } else {
                $row['add']=$add.$paramid.'.no:1';    
            }
            $block['row'][]=$row;
        }
        if($block['type']=='string'){
            foreach($param['option']['values'] as $value){
                $row=array(
                    'title'=>$value['title'],
                    'count'=>$value['filter']
                );
                $row['checked']=!!$mymd[$value['id']];
                $valueid=infra_seq_short(array(Catalog::urlencode($value['id'])));
                if($row['checked']){
                    $row['add']=$add.$paramid.'.'.$valueid.':';
                } else {
                    $row['add']=$add.$paramid.'.'.$valueid.':1';
                }
                
                $block['row'][]=$row;
            }
        }
        $ans['template'][]=$block;
    }
    
	return $ans;
}, $args, isset($_GET['re']));
$ans=array_merge($ans, $res);

return infra_ret($ans);
