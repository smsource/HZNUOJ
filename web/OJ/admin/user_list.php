<?php
  /**
   * This file is created
   * by lixun516@qq.com
   * @2020.02.03
  **/
?>

<?php require_once("admin-header.php");?>
<?php
require_once("../include/set_get_key.php");

//分页start
$page = 1;
$args=array();
if(isset($_GET['page'])) $page = intval($_GET['page']);
if(isset($_GET['team'])) $args['team'] = $_GET['team']; else $args['team']="";
if(isset($_GET['contest'])) $args['contest']=$_GET['contest']; else $args['contest']="";
if(isset($_GET['class'])) $args['class']=$_GET['class']; else $args['class']="";
if(isset($_GET['sort_method'])) $args['sort_method']=$_GET['sort_method']; else $args['sort_method']="";
if(isset($_GET['keyword'])) $args['keyword']=$_GET['keyword']; else $args['keyword']="";
if(isset($page)) $args['page']=$page;
function generate_url($data,$link){
    global $args;
    if($link=="") $link="user_list.php?"; else $link.="?";
    foreach ($args as $key => $value) {
        if(isset($data["$key"])){
            $value=htmlentities($data["$key"]);
            $link.="&$key=$value";
        }
        else if($value){
            $link.="&$key=".htmlentities($value);
        }
    }
    return $link;
}
//分页end 
$sql_filter = "WHERE 1";
if(isset($_GET['keyword']) && $_GET['keyword']!=""){
    $keyword = $mysqli->real_escape_string($_GET['keyword']);
    $keyword = "'%$keyword%'";
    if(!isset($_GET['team'])) {
        $sql_filter .= " AND ((user_id LIKE $keyword ) OR (nick LIKE $keyword ) OR (school LIKE $keyword ) OR (email LIKE $keyword ))";
    } else {
        $sql_filter .= " AND ((a.user_id LIKE $keyword ) OR (nick LIKE $keyword ) OR (school LIKE $keyword ) OR (institute LIKE $keyword ))";
    }
    if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){
        $sql_filter .=" OR (real_name LIKE $keyword ) ";
    }
}
if(isset($_GET['team'])){
    if($_GET['team']!="all" && $_GET['team']!="") $sql_filter .= " AND `prefix`= '{$mysqli->real_escape_string($_GET['team'])}' ";
    if($_GET['contest']!="all" && $_GET['contest']!="") $sql_filter .= " AND a.`contest_id`= {$mysqli->real_escape_string($_GET['contest'])} ";
}
if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE 
   && isset($_GET['class']) && $_GET['class']!="all" && $_GET['class']!=""){
    $sql_filter .= " AND `class`= '{$mysqli->real_escape_string($_GET['class'])}' ";
}
switch ($args['sort_method']) {
    case 'AccTime_DESC':
        $acctime_icon="am-icon-sort-amount-desc";
        $regtime_icon="am-icon-sort";
        $strength_icon="am-icon-sort";
        $sql_filter .= " ORDER BY `accesstime` DESC,user_id ";
        $accTime = 'AccTime_ASC';
        $regTime = 'RegTime_DESC';
        $strength = 'strength_DESC';
        break;
    case 'AccTime_ASC':
        $acctime_icon="am-icon-sort-amount-asc";
        $regtime_icon="am-icon-sort";
        $strength_icon="am-icon-sort";
        $sql_filter .= " ORDER BY `accesstime`,user_id ";
        $accTime = 'AccTime_DESC';
        $regTime = 'RegTime_DESC';
        $strength = 'strength_DESC';
        break;
    case 'RegTime_ASC':
        $acctime_icon="am-icon-sort";
        $regtime_icon="am-icon-sort-amount-asc";
        $strength_icon="am-icon-sort";
        $sql_filter .= " ORDER BY `reg_time`,user_id ";
        $accTime = 'AccTime_DESC';
        $regTime = 'RegTime_DESC';
        $strength = 'strength_DESC';
        break;
    case 'RegTime_DESC': default:
        $acctime_icon="am-icon-sort";
        $regtime_icon="am-icon-sort-amount-desc";
        $strength_icon="am-icon-sort";
        $sql_filter .= " ORDER BY `reg_time` DESC,user_id ";
        $accTime = 'AccTime_DESC';
        $regTime = 'RegTime_ASC';
        $strength = 'strength_DESC';
        break;
    case 'strength_DESC':
        $acctime_icon="am-icon-sort";
        $regtime_icon="am-icon-sort";
        $strength_icon="am-icon-sort-amount-desc";
        $sql_filter .= " ORDER BY `strength` DESC,user_id ";
        $accTime = 'AccTime_DESC';
        $regTime = 'RegTime_DESC';
        $strength = 'strength_ASC';
        break;   
    case 'strength_ASC':
        $acctime_icon="am-icon-sort";
        $regtime_icon="am-icon-sort";
        $strength_icon="am-icon-sort-amount-asc";
        $sql_filter .= " ORDER BY `strength`,user_id ";
        $accTime = 'AccTime_DESC';
        $regTime = 'RegTime_DESC';
        $strength = 'strength_DESC';
        break;  
}
if(!isset($_GET['team']))
    $sql = "SELECT COUNT('user_id') FROM `users` ".$sql_filter;
else
    $sql = "SELECT COUNT('user_id') FROM `team` as a ".$sql_filter;
$result =$mysqli->query($sql)->fetch_all();
$total = 0;
if($result) $total = $result[0][0];
$page_cnt = 50;
$view_total_page = ceil($total/$page_cnt);//计算页数
if($page > $view_total_page && $view_total_page>0) $args['page'] = $page = $view_total_page;
$left_bound = $page_cnt*$page-$page_cnt;
$u_id = $left_bound;

$sql_filter .= " LIMIT $left_bound, $page_cnt";
$view_users=Array();
$cnt=0;
$colspan = (isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE)?16:13;
if(!isset($_GET['team'])) { //查询普通账号
    require_once("../include/my_func.inc.php");
    $sql = "SELECT `user_id`,`nick`,`defunct`,`accesstime`,`reg_time`,`ip`,`email`,`school`,`stu_id`,`class`,`real_name`,`strength`,`level` FROM `users` ".$sql_filter;
    //echo $sql;    
    $result =$mysqli->query($sql);
    while ($row=$result->fetch_object()){
        if (HAS_PRI("edit_user_profile")) $view_users[$cnt][0] = "<input type=checkbox name='cid[]' value='$row->user_id' />&nbsp;".++$u_id;
            else $view_users[$cnt][0] = ++$u_id;
        $view_users[$cnt][1] = "<a href='../userinfo.php?user=".$row->user_id."' target='_blank'>".$row->user_id."</a>";
        $view_users[$cnt][2] = $row->nick;
        if (HAS_PRI("edit_user_profile")){
            if($row->user_id != 'admin' && $row->user_id != $_SESSION['user_id']){
                if($row->defunct=="N"){
                    $view_users[$cnt][3] = "<a href='user_df_change.php?cid=".$row->user_id."&getkey=".$_SESSION['getkey']."'>".$MSG_Available."</a>";
                } else {
                    $view_users[$cnt][3] = "<a href='user_df_change.php?cid=".$row->user_id."&getkey=".$_SESSION['getkey']."'>".$MSG_Reserved."</a>";
                }
                $view_users[$cnt][4] = get_group($row->user_id);
                if(!IS_ADMIN($row->user_id)){
                    $view_users[$cnt][5] = "<a href='#' onclick='javascript:if(confirm(\" $MSG_DEL ?\")) location.href=\"user_edit.php?del&cid=$row->user_id&getkey={$_SESSION['getkey']}\"'>$MSG_DEL</a>";
                } else $view_users[$cnt][5] = $MSG_DEL;
            } else {
                if($row->defunct=="N"){
                    $view_users[$cnt][3] = $MSG_Available;
                } else {
                    $view_users[$cnt][3] = $MSG_Reserved;
                }
                $view_users[$cnt][4] = get_group($row->user_id);
                $view_users[$cnt][5] = $MSG_DEL;
            }
            if($row->user_id != $_SESSION['user_id'] && get_order(get_group($row->user_id))<=get_order(get_group(""))){
                $view_users[$cnt][6] = $MSG_EDIT;
            } else $view_users[$cnt][6] = "<a href='".generate_url("","user_edit.php")."&cid=$row->user_id'>$MSG_EDIT</a>";
            if(!IS_ADMIN($row->user_id)){
                $view_users[$cnt][7] = "<a href='changepass.php?cid=$row->user_id' target='_blank'>$MSG_SETPASSWORD</a>";
            } else $view_users[$cnt][7] = $MSG_SETPASSWORD;
        } else {
            if($row->defunct=="N"){
                $view_users[$cnt][3] = $MSG_Available;
            } else {
                $view_users[$cnt][3] = $MSG_Reserved;
            }
            $view_users[$cnt][4] = get_group($row->user_id);
        }
        $view_users[$cnt][8] = round($row->strength);
        $view_users[$cnt][9] = $row->level;
        $view_users[$cnt][10] = $row->accesstime;
        $view_users[$cnt][11] = $row->reg_time;
        $view_users[$cnt][12] = $row->ip;
        $view_users[$cnt][13] = $row->email;
        $view_users[$cnt][14] = $row->school;
        if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){
            $view_users[$cnt][15] = $row->stu_id;
            $view_users[$cnt][16] = $row->real_name;
            $view_users[$cnt][17] = $row->class;
        }
        $cnt++;
    }
} else { //查询比赛临时账号
    $sql = "SELECT a.`user_id`,a.`nick`,a.`contest_id`, `contest`.`title`, `contest`.`defunct`,a.`school`,a.`class`,a.`stu_id`,a.`real_name`,a.`accesstime`,a.`reg_time`,a.`ip`,a.`institute`,a.`seat` FROM `team` as a ";
    $sql .= " LEFT JOIN `contest` ON a.`contest_id` = `contest`.`contest_id`";
    $sql .=$sql_filter;
    //echo $sql;
    //exit(0);
    $result =$mysqli->query($sql);
    while ($result && $row=$result->fetch_object()){
        if (HAS_PRI("edit_user_profile")) $view_users[$cnt][0] = "<input type=checkbox name='cid[]' value='$row->user_id' />&nbsp;".++$u_id;
            else $view_users[$cnt][0] = ++$u_id;
        $view_users[$cnt][1] = $row->user_id;
        $view_users[$cnt][2] = $row->nick;
        if (HAS_PRI("edit_user_profile")){
            $view_users[$cnt][3] = "<a href='#' onclick='javascript:if(confirm(\" $MSG_DEL ?\")) location.href=\"user_edit.php?team&del&cid=$row->user_id&getkey={$_SESSION['getkey']}\"'>$MSG_DEL</a>";
            $view_users[$cnt][4] = "<a href='".generate_url("","user_edit.php")."&cid=$row->user_id'>$MSG_EDIT</a>";
            $view_users[$cnt][5] = "<a href='user_edit.php?resetpwd&cid=$row->user_id&getkey={$_SESSION['getkey']}'>$MSG_RESET$MSG_PASSWORD</a>";
        }
        $contest_status = ($row->defunct=='Y')?'<font color=red>【'.$MSG_Reserved.'】</font>':"";
        $view_users[$cnt][6] = ($row->title)?"<a href='../status.php?cid=$row->contest_id' target='_blank'>【{$row->contest_id}】$row->title $contest_status</a>" : "【{$row->contest_id}】";
        $view_users[$cnt][7] = $row->accesstime;
        $view_users[$cnt][8] = $row->reg_time;;
        $view_users[$cnt][9] = $row->ip;
        $view_users[$cnt][10] = $row->school;
        if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){
            $view_users[$cnt][11] = $row->stu_id;
            $view_users[$cnt][12] = $row->real_name;
            $view_users[$cnt][13] = $row->class;
        }
        $view_users[$cnt][14] = $row->seat;
        $view_users[$cnt][15] = $row->institute;
        $cnt++;
    }    
}

?>
<title><?php echo $html_title.$MSG_USER.$MSG_LIST?></title>
<h1><?php echo $MSG_USER.$MSG_LIST ?></h1>
<h4><?php echo $MSG_HELP_USER_LIST ?></h4>
<div class="am-avg-md-1" style="margin-top: 20px; margin-bottom: 20px;">
    <ul class="am-nav am-nav-tabs">
        <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
        <?php if(isset($_GET['team'])){ ?>
        <li><a href="user_list.php"><?php echo $MSG_USER.$MSG_LIST ?></a></li>
        <li class="am-active"><a href="user_list.php?team=all"><?php echo $MSG_TEAM.$MSG_LIST ?></a></li>
        <?php } else { ?>
        <li class="am-active"><a href="user_list.php"><?php echo $MSG_USER.$MSG_LIST ?></a></li>
        <li><a href="user_list.php?team=all"><?php echo $MSG_TEAM.$MSG_LIST ?></a></li>
        <?php } ?>
        </ul>
</div>
<!-- 查找 start -->
<div class='am-g'>
    <div class='am-u-md-12'>
        <form id= "searchform" class="am-form am-form-inline">
          <?php if (isset($_GET['team'])) { ?>
            <div class='am-form-group'>
                <select class="selectpicker show-tick" data-live-search="true" id='team' name='team' data-width="auto" onchange='javascript:document.getElementById("searchform").submit();'>
                    <option value='all' <?php if (isset($_GET['team']) && ($_GET['team']=="" || $_GET['team']=="all")) echo "selected"; ?>> <?php echo $MSG_ALL ?></option>
                <?php
                    $sql = "SELECT DISTINCT `prefix` FROM `team` ORDER BY `prefix`";
                    $result = $mysqli->query($sql);
                    $prefix = $result->fetch_all();
                    $result->free();
                    foreach($prefix as $row){
                        echo "<option value='".$row[0]."' ";
                        if (isset($_GET['team']) && $_GET['team']==$row[0])  echo "selected";
                        echo ">".$row[0]."</option>";
                    }
                ?>
                </select>                
            </div>
            <div class='am-form-group'>
                <select class="selectpicker show-tick" data-live-search="true" id='contest' name='contest' data-width="auto" onchange='javascript:document.getElementById("searchform").submit();'>
                    <option value='all' <?php if (isset($_GET['contest']) && ($_GET['contest']=="" || $_GET['contest']=="all")) echo "selected"; ?>> <?php echo $MSG_ALL ?></option>
                <?php
                    $sql = "SELECT DISTINCT `team`.`contest_id`, `contest`.`title`,`contest`.`defunct` FROM `team`";
                    $sql .= " LEFT JOIN `contest` ON `team`.`contest_id` = `contest`.`contest_id` ORDER BY `team`.`contest_id` desc";
                    $result = $mysqli->query($sql);
                    $contest = $result->fetch_all(MYSQLI_ASSOC);
                    $result->free();
                    foreach($contest as $row){
                        echo "<option value='".$row['contest_id']."' ";
                        if (isset($_GET['contest']) && $_GET['contest']==$row['contest_id'])  echo "selected";
                        $contest_status = ($row['defunct']=='Y')?'【'.$MSG_Reserved.'】':"";
                        echo ">【".$row['contest_id']."】".$row['title'].$contest_status."</option>";
                    }
                ?>
                </select>                
            </div>
          <?php } 
          if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){
          ?>
          <div class='am-form-group'>
                <select class="selectpicker show-tick" data-live-search="true" id='class' name='class' data-width="auto" onchange='javascript:document.getElementById("searchform").submit();'>
                    <option value='all' <?php if (isset($_GET['class']) && ($_GET['class']=="" || $_GET['class']=="all")) echo "selected"; ?>> <?php echo $MSG_ALL ?></option>
                <?php
                    if (isset($_GET['team'])) {
                        $sql = "SELECT DISTINCT `class` FROM `team` ORDER BY `class`";
                    } else $sql = "SELECT DISTINCT `class` FROM `users` ORDER BY `class`";
                    $result = $mysqli->query($sql);
                    $prefix = $result->fetch_all();
                    $result->free();
                    foreach($prefix as $row){
                        echo "<option value='".$row[0]."' ";
                        if (isset($_GET['class']) && $_GET['class']==$row[0])  echo "selected";
                        echo ">".$row[0]."</option>";
                    }
                ?>
                </select>                
            </div>
        <?php } ?>
            <div class="am-form-group am-form-icon">  
                <i class="am-icon-search"></i>
                <input class="am-form-field" name="keyword" type="text"  placeholder="<?php echo $MSG_KEYWORDS ?>" value="<?php echo $args['keyword'] ?>"/>
            </div>
            <input class="btn btn-default" type=submit value="<?php echo $MSG_SEARCH?>" >
        </form>
    </div>
</div>
                      
<!-- 查找 end -->

<!-- 页标签 start -->
<div class="am-g">
    <ul class="pagination text-center" style="margin-top: 10px;margin-bottom: 0px;">
    <?php $link = generate_url(Array("page"=>max($page-1, 1)),"")?>
      <li><a href="<?php echo $link ?>">&laquo; Prev</a></li>
        <?php
        //分页
        for ($i=1;$i<=$view_total_page;$i++){
            $link=generate_url(Array("page"=>"$i"),"");
            if($page==$i)
                echo "<li class='active'><a href=\"$link\">{$i}</a></li>";
            else
                echo "<li><a href=\"$link\">{$i}</a></li>";
        }
      ?>
        <?php $link = generate_url(Array("page"=>min($page+1,intval($view_total_page))),"") ?>
      <li><a href="<?php echo $link ?>">Next &raquo;</a></li>
  </ul>
</div>
<!-- 页标签 end -->
<style type="text/css" media="screen">
    #acctime:hover, #regtime, #strength:hover{
        cursor: pointer;
    }
</style>
<?php if(!isset($_GET['team'])) { ?>
    <!-- 罗列普通用户 start -->
<div class="am-g am-scrollable-horizontal" style="max-width: 1300px;">
<form action="user_df_change.php?getkey=<?php echo $_SESSION['getkey'] ?>" method='post' >
    <table class="table table-hover table-bordered table-condensed table-striped" style="white-space: nowrap;">
    <thead>
      <?php if (HAS_PRI("edit_user_profile")) { ?>
        <tr>
            <td colspan=<?php echo $colspan+2 ?>>
            <input type=submit name='enable' class='btn btn-default' value='<?php echo $MSG_Available ?>'>&nbsp;
            <input type=submit name='disable' class='btn btn-default' value='<?php echo $MSG_Reserved ?>'>
            </td>
        </tr>
        <tr>
            <th width="10px"><input type=checkbox style='vertical-align:2px;' onchange='$("input[type=checkbox]").prop("checked", this.checked)'>&nbsp;<?php echo $MSG_ID ?></th>
      <?php } else { ?>
      <tr>
            <th width="10px"><?php echo $MSG_ID ?></th>
      <?php } ?>
      <th><?php echo $MSG_USER_ID ?></th>
      <th><?php echo $MSG_NICK ?></th>
      <th><?php echo $MSG_STATUS ?></th>
      <th><?php echo $MSG_PRIVILEGE ?></th>
      <?php if (HAS_PRI("edit_user_profile")) { ?>
        <th colspan="3" style="text-align: center"><?php echo $MSG_Operations ?></th> 
      <?php } ?>
      <th id="strength"><?php echo $MSG_STRENGTH ?>&nbsp;<span class="<?php echo $strength_icon ?>"></span></th>
      <th><?php echo $MSG_LEVEL ?></th>
      <th id="acctime"><?php echo $MSG_AccessTime ?>&nbsp;<span class="<?php echo $acctime_icon ?>"></span></th>
      <th id="regtime"><?php echo $MSG_RegTime ?>&nbsp;<span class="<?php echo $regtime_icon ?>"></span></th>
      <th><?php echo $MSG_RegIP ?></th>
      <th><?php echo $MSG_EMAIL ?></th>
      <th><?php echo $MSG_SCHOOL ?></th>
      <?php if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){ ?>
        <th><?php echo $MSG_StudentID ?></th>
        <th><?php echo $MSG_REAL_NAME ?></th>
        <th><?php echo $MSG_Class ?></th>
      <?php } ?>
      </tr>
      </thead>
      <tbody>
      <?php
      foreach($view_users as $row){
          echo "<tr>\n";
          foreach($row as $table_cell){
              echo "<td>";
              echo $table_cell;
              echo "</td>\n";
          }
          echo "</tr>\n";
      }
      ?>
      </tbody>
    </table>
    </form>
</div>
<!-- 罗列普通用户 end -->
    <?php } else { ?>
<!-- 罗列比赛账号 start -->
<div class="am-g am-scrollable-horizontal" style="max-width: 1300px;">
<form method='post'>
    <table class="table table-hover table-bordered table-condensed table-striped" style="white-space: nowrap;">
    <thead>
      <?php if (HAS_PRI("edit_user_profile")) { ?>
        <tr>
            <td colspan=<?php echo $colspan ?>>
            <input type=submit name='delete' class='btn btn-default' value='<?php echo $MSG_DEL ?>' onclick='javascript:if(confirm("<?php echo $MSG_DEL ?>?")) $("form").attr("action","user_edit.php?team&del&getkey=<?php echo $_SESSION['getkey'] ?>");'>&nbsp;
            <input type=submit name='resetpwd' class='btn btn-default' value='<?php echo $MSG_RESET.$MSG_PASSWORD ?>' onclick='javascript:if(confirm("<?php echo $MSG_RESET.$MSG_TEAM.$MSG_PASSWORD ?>?")) $("form").attr("action","user_edit.php?resetpwd&getkey=<?php echo $_SESSION['getkey'] ?>");'>
            </td>
        </tr>
      <tr>
        <th width="10px"><input type=checkbox style='vertical-align:2px;' onchange='$("input[type=checkbox]").prop("checked", this.checked)'>&nbsp;<?php echo $MSG_ID ?></th>
      <?php } else { ?>
      <tr>
            <th width="10px"><?php echo $MSG_ID ?></th>
      <?php } ?>
      <th><?php echo $MSG_USER_ID ?></th>
      <th><?php echo $MSG_NICK ?></th>
      <?php if (HAS_PRI("edit_user_profile")) { ?>
        <th colspan="3" style="text-align: center"><?php echo $MSG_Operations ?></th>
      <?php } ?>
      <th><?php echo $MSG_CONTEST ?></th>
      <th id="acctime"><?php echo $MSG_AccessTime ?>&nbsp;<span class="<?php echo $acctime_icon ?>"></span></th>
      <th id="regtime"><?php echo $MSG_RegTime ?>&nbsp;<span class="<?php echo $regtime_icon ?>"></span></th>
      <th><?php echo $MSG_RegIP ?></th>
      <th><?php echo $MSG_SCHOOL ?></th>
      <?php if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){ ?>
        <th><?php echo $MSG_StudentID ?></th>
        <th><?php echo $MSG_REAL_NAME ?></th>
        <th><?php echo $MSG_Class ?></th>
      <?php } ?>
      <th><?php echo $MSG_Seat ?></th> 
      <th><?php echo $MSG_Institute ?></th>
      </tr>
      </thead>
      <tbody>
      <?php
      foreach($view_users as $row){
          echo "<tr>\n";
          foreach($row as $table_cell){
              echo "<td>";
              echo $table_cell;
              echo "</td>\n";
          }
          echo "</tr>\n";
      }
      ?>
      </tbody>
    </table>
    </form>
</div>
<!-- 罗列比赛账号 end -->
    <?php }; ?>

<!-- 页标签 start -->
<div class="am-g">
    <ul class="pagination text-center" style="margin-top: 1px;margin-bottom: 0px;">
    <?php $link = generate_url(Array("page"=>max($page-1, 1)),"")?>
      <li><a href="<?php echo $link ?>">&laquo; Prev</a></li>
        <?php
        //分页
        for ($i=1;$i<=$view_total_page;$i++){
            $link=generate_url(Array("page"=>"$i"),"");
            if($page==$i)
                echo "<li class='active'><a href=\"$link\">{$i}</a></li>";
            else
                echo "<li><a href=\"$link\">{$i}</a></li>";
        }
      ?>
        <?php $link = generate_url(Array("page"=>min($page+1,intval($view_total_page))),"") ?>
      <li><a href="<?php echo $link ?>">Next &raquo;</a></li>
  </ul>
</div>
<!-- 页标签 end -->

<?php 
    //require_once("../template/".$OJ_TEMPLATE."/footer.php");
  require_once("admin-footer.php")
?>
<!-- sort by acctime、regtime BEGIN -->
<script>
     <?php $args['sort_method']= $accTime; ?>
  $("#acctime").click(function(){   
    var link="<?php echo generate_url(Array("page"=>"1"),"") ?>";
    window.location.href=link;
  });
  <?php $args['sort_method']= $regTime; ?>
  $("#regtime").click(function(){   
    var link="<?php echo generate_url(Array("page"=>"1"),"") ?>";
    window.location.href=link;
  });
  <?php $args['sort_method']= $strength; ?>
  $("#strength").click(function(){   
    var link="<?php echo generate_url(Array("page"=>"1"),"") ?>";
    window.location.href=link;
  });
</script>
<!-- sort by acctime、regtime  END -->