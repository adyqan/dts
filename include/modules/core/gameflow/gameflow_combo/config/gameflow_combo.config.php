<?php

namespace gameflow_combo
{
	//连斗时的人数限制
	$combolimit = 50;
	//连斗最小死亡人数限制a
	$deathlimit = 160;
	//连斗激活系数分母b
	$deathdeno = 20;
	//连斗激活系数分子c。如果设参与人数为d，则实际连斗判定死亡数是a+ceil(d/b)*c
	$deathnume = 20;
}

?>