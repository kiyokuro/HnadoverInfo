<?php
/*
・気温データをDBにインサートする。インサートしたレコードをjsonで返す。
・パラメータはair_temp,machine_id
・result_code 1:成功、2:パラメータがない
・result_count 検索結果のレコード数
*/
require(__DIR__ . '/DbInfo.php');

$dbInfo = new DbInfo();

//パラメータを取得
if(isset($_GET['air_temp'])) {
      $air_temp = $_GET['air_temp'];
}
if(isset($_GET['machine_id'])) {
      $machine_id = $_GET['machine_id'];
}
//YYYY/MM/DD hh:mmで時間を取得
$dateTime = date('Y/m/d H:i');

try{
    //DBに接続
    $mysqli = new mysqli('サーバ名', 'ユーザー名','パスワード', 'データベース名');
    $mysqli->set_charset('utf8');
    if(empty($air_temp) || empty($machine_id)){
      $result_code = 2;
    }else{
      //クエリ実行
      $mysqli->query("insert into sensor_datas (created_at, machine_id, air_temp) values ($dateTime, $machine_id, $air_temp);");
      $results = $mysqli->query("select * from sensor_datas where created_at = $dateTime;");
      $result_code = 1;
    }

    $result = makeResult($results,$result_code);
    $json = returnJson($result);
    echo $json;

    //disconnect普通はプログラム終了時に勝手に切れる。明示的に切る時の処理。
    $mysqli->close();
}catch(Exception $e){
    echo $e->getMessage();
    exit;
}

function makeResult($results,$result_code){
    $result_list = [];
    foreach ($results as $row) {
      $result_list[] = ['id'=>$row[id],'created_at'=>$row[created_at],
      'machine_id'=>$row[machine_id],'air_temp'=>$row[air_temp]];
    }
    $result = [
      'result_code' => $result_code,
      'result_count' => count($result_list),
      'data' => $result_list
    ];
  return $result;
}

function returnJson($resultArray){
  if(array_key_exists('callback', $_GET)){
    $json = $_GET['callback'] . "(" . json_encode($resultArray) . ");";
  }else{
    $json = json_encode($resultArray,  JSON_UNESCAPED_UNICODE);
  }
  //header('Content-Type: text/html; charset=utf-8');
  return $json;
}
