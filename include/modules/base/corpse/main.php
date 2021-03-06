<?php

namespace corpse
{
	function init() {}
	
	//检查该尸体是否是发现的合法目标
	//Q：为什么要写这么一个脑残的函数？
	//A：尊重原版本设定，如果遇到了不是合法目标的尸体是要直接重新执行一次discover全判定流程的
	//   虽然这设定非常奇葩，但改了可能产生大量的平衡性问题，因此保留
	function check_corpse_discover(&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','corpse'));
		if ($edata['state']==16) return 0;
		$flag=0;
		foreach($equip_list as $k_value)
		{
			$z=strlen($k_value)-1;
			while ('0'<=$k_value[$z] && $k_value[$z]<='9') $z--;
			$w1=substr($k_value,0,$z+1).'s'.substr($k_value,$z+1);
			$w2=substr($k_value,0,$z+1).'e'.substr($k_value,$z+1);
			if ($edata[$w1] && $edata[$w2]) { $flag=1; break;}
		}
		if ($edata['money']) $flag=1;
		
		if($flag && $edata['endtime'] < $now - $corpseprotect)
			return 1;
		else  return 0;
	}
	
	function check_corpse_discover_dice(&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('corpse'));
		$corpse_dice = rand(0,99);
		if ($corpse_dice < $corpse_obbs) return 1; else return 0;
	}
	
	function findcorpse(&$edata){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','itemmain','metman','logger'));
		$battle_title = '发现尸体';
		extract($edata,EXTR_PREFIX_ALL,'w');
		\metman\init_battle(1);
		
		$main = MOD_METMAN_MEETMAN;
		$log .= '你发现了<span class="red">'.$w_name.'</span>的尸体！<br>';
		
		$r=\itemmain\parse_item_words($edata,1);
		extract($r,EXTR_PREFIX_ALL,'w');
		
		include template(get_corpse_filename());
		$cmd = ob_get_contents();
		ob_clean();
		return;
	}
	
	function get_corpse_filename(){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','logger','player','metman'));
		return MOD_CORPSE_CORPSE;
	}
	
	function meetman($sid)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','logger','player','metman'));
		
		$edata=\player\fetch_playerdata_by_pid($sid);
		if ($edata['hp']<=0)
		{
			$action = 'corpse'.$sid;
			findcorpse($edata);
			return;
		}
		else  $chprocess($sid);
	}
	
	function prepare_initial_response_content()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','metman'));
		$cmd = $main = '';
		if((strpos($action,'corpse')===0 || strpos($action,'pacorpse')===0) && $gamestate<40){
			$cid = strpos($action,'corpse')===0 ? str_replace('corpse','',$action) : str_replace('pacorpse','',$action);
			if($cid){
				$result = $db->query("SELECT * FROM {$tablepre}players WHERE pid='$cid' AND hp=0");
				if($db->num_rows($result)>0){
					$edata = \player\fetch_playerdata_by_pid($cid);
					extract($edata,EXTR_PREFIX_ALL,'w');
					findcorpse($edata);
					\metman\init_battle(1);
					$main = MOD_METMAN_MEETMAN;
				}
			}	
		}
		
		$chprocess();
	}
	
	function getcorpse_action(&$edata, $item)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','logger'));
		
		if($item == 'wep') {
			$itm0 = $edata['wep'];
			$itmk0 = $edata['wepk'];
			$itme0 = $edata['wepe'];
			$itms0 = $edata['weps'];
			$itmsk0 = $edata['wepsk'];
			$edata['wep'] = $edata['wepk'] = $edata['wepsk'] = '';
			$edata['wepe'] = $edata['weps'] = 0;  
		} elseif(strpos($item,'ar') === 0) {
			$itm0 = $edata[$item];
			$itmk0 = $edata[$item.'k'];
			$itme0 = $edata[$item.'e'];
			$itms0 = $edata[$item.'s'];
			$itmsk0 = $edata[$item.'sk'];
			$edata[$item] = $edata[$item.'k'] = $edata[$item.'sk'] = '';
			$edata[$item.'e'] = $edata[$item.'s'] = 0;  
		} elseif(strpos($item,'itm') === 0) {
			$itmn = substr($item,3,1);
			$itm0 = $edata['itm'.$itmn];
			$itmk0 = $edata['itmk'.$itmn];
			$itme0 = $edata['itme'.$itmn];
			$itms0 = $edata['itms'.$itmn];
			$itmsk0 = $edata['itmsk'.$itmn];
			$edata['itm'.$itmn] = $edata['itmk'.$itmn] = $edata['itmsk'.$itmn] = '';
			$edata['itme'.$itmn] = $edata['itms'.$itmn] = 0;  
		} elseif($item == 'money') {
			$money += $edata['money'];
			$log .= '获得了金钱 <span class="yellow">'.$edata['money'].'</span>。<br>';
			$edata['money'] = 0;
			\player\player_save($edata);
			$action = '';
			$mode = 'command';
			return;
		} elseif($item == 'destroy') {
			$edata['weps'] = 0;
			$edata['arbs'] = 0;
			$edata['arhs'] = 0;
			$edata['aras'] = 0;
			$edata['arfs'] = 0;
			$edata['arts'] = 0;
			$edata['itms0'] = 0;
			$edata['itms1'] = 0;
			$edata['itms2'] = 0;
			$edata['itms3'] = 0;
			$edata['itms4'] = 0;
			$edata['itms5'] = 0;
			$edata['itms6'] = 0;
			$edata['money'] = 0;
			$edata['state'] = 16;
			\player\player_save($edata);
			$log .= '尸体成功销毁！';
			$action = '';
			$mode = 'command';
			return;
		} else {
			$mode = 'command';
			$action = '';
			return;
		}
		if(!$itms0||!$itmk0||$itmk0=='WN'||$itmk0=='DN') {
			$log .= '该物品不存在！';
		} else {
			\itemmain\itemget();
		}
		$action = '';
		$mode = 'command';
	}
	
	function getcorpse($item){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','logger'));
		
		$corpseid = strpos($action,'corpse')===0 ? str_replace('corpse','',$action) : str_replace('pacorpse','',$action);
		if(!$corpseid || strpos($action,'corpse')===false){
			$log .= '<span class="yellow">你没有遇到尸体，或已经离开现场！</span><br>';
			$action = '';
			$mode = 'command';
			return;
		}

		$result = $db->query("SELECT * FROM {$tablepre}players WHERE pid='$corpseid'");
		if(!$db->num_rows($result)){
			$log .= '对方不存在！<br>';
			$action = '';
			$mode = 'command';
			return;
		}

		//$edata = $db->fetch_array($result);
		$edata=\player\fetch_playerdata_by_pid($corpseid);
		
		if($edata['hp']>0) {
			$log .= '对方尚未死亡！<br>';
			$action = '';
			$mode = 'command';
			return;
		} elseif($edata['pls'] != $pls) {
			$log .= '对方跟你不在同一个地图！<br>';
			$action = '';
			$mode = 'command';
			return;
		}
		
		getcorpse_action($edata,$item);
		
		\player\player_save($edata);

		return;
	}

	function act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player'));
		if($mode == 'corpse') {
			getcorpse($command);
			return;
		}
		
		if (strpos($action,'pacorpse')===0)
		{
			$cid = str_replace('pacorpse','',$action);
			if($cid){
				$result = $db->query("SELECT * FROM {$tablepre}players WHERE pid='$cid' AND hp=0");
				if($db->num_rows($result)>0){
					$edata = \player\fetch_playerdata_by_pid($cid);
					findcorpse($edata);
					return;
				}	
			}	
		}
		
		$chprocess();
	}

}

?>
