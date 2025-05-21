
<?php

require_once('connect.php');

define('BASE_PATH', '/php-exercice/index.php/');
define('TABLE_PLANT', 'plants');

$db = ConnectBdd('plant_db');

ApplyHead();
Main();
ApplyFooter();

function Main():void
{
    $requests = GetRequestURI();
    if (count($requests) == 0)
    {
        Page_Home();
        return;
    }
        
    switch($requests[0])
    {
        case 'home':
            Page_Home();
            break;
        case 'view':
            Page_View($requests[1]);
            break;
        case 'edit':
            Page_Edit($requests[1]);
            break;
        case 'delete':
            Page_Delete($requests[1]);
            break;
        case 'add':
            Page_Add();
            break;
    }
}

function Page_Home():void
{
    global $db;
    $results = FetchAll($db, TABLE_PLANT);
    GenerateTable($results);
}

function Page_Add():void
{
    global $db;

    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        $columns = GetColumns($db, TABLE_PLANT);

        $str = '<form method="POST" action="">';

        foreach ($columns as $item)
        {
            $name = $item['COLUMN_NAME'];
            if ($name === 'id') 
                continue;

            $type = $item['DATA_TYPE'];

            $input = '<input name="' . $name . '" placeholder="' . $name . '" ';

            if ($type === 'int' || $type === 'double')
            {
                $input .= 'type="number"';
            }
            else if ($type === 'date')
            {
                $input .= 'type="date"';
            }

            $input .= '/><br>';

            $str .= $input;
        }

        $str .= '<input type="submit" value="Save"/>';
        $str .= '</form><br>';

        echo $str;
    }
    else if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        header('Location: ' . BASE_PATH);
    }
}

function Page_View($id):void
{
    global $db;

    echo '<a href="' . BASE_PATH . '"> Back to Home</a></br></br>';
    echo '<b>Details</b></br>';

    $result = FetchById($db, TABLE_PLANT, $id);
    foreach($result as $col => $value)
    {
        echo $col . ': ' . $value . '</br>';
    }
}

function Page_Delete($id):void
{
    global $db;
    DeleteById($db, TABLE_PLANT, $id);
    header('Location: ' . BASE_PATH);
}

function Page_Edit($id):void
{
    global $db;
    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        ShowEditForm($id);
    }
    else if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        ChangeEdit($_POST, $id);
        header('Location: ' . BASE_PATH);
    }
}

function ShowEditForm($id)
{
    global $db;

    $result = FetchById($db, TABLE_PLANT, $id);
    if (!$result) 
    {
        echo 'Invalid ID';
        die;
    }

    $columns = GetColumns($db, TABLE_PLANT);

    $str = '<form method="POST" action="">';

    foreach ($columns as $col) 
    {
        $columnName = $col['COLUMN_NAME'];

        if ($columnName === 'id')
            continue;

        $inputStr = '<input name="' . $columnName . '" placeholder="' . $columnName . '" value="' . $result[$columnName] . '" ';

        $columnType = $col['DATA_TYPE'];
        if ($columnType === 'int' || $columnType === 'double') 
        {
            $inputStr .= 'type="number" ';
        }

        if ($columnType === 'date') 
        {
            $inputStr .= 'type="date" ';
        }

        $inputStr .= '/>';

        $str .= $inputStr;
        $str .= '<br>';
    }
    $str .= '<input type="submit" value="Save" />';
    $str .= '</form>';
    echo $str;
}

function ChangeEdit($data, $id)
{
    global $db;

    $columns = array_keys($data);
    $sql = 'UPDATE ' . TABLE_PLANT . ' SET ';
    foreach($columns as $col)
    {
        $sql .= $col . ' = :' . $col . ', ';
    }

    $sql = rtrim($sql, ', ');
    $sql .= ' WHERE id = :id';

    try
    {
        $query = $db->prepare($sql);
        foreach($data as $key => $value)
        {
            $query->bindValue(':' . $key, $value !== '' ? $value : null);
            echo 'value => ' . $value . '<br>';
        }
        $query->bindValue(':id', $id);
        $query->execute();
    }
    catch (PDOException $e)
    {
        echo 'Error: ' . $e->getMessage();
        die;
    }
}

function GenerateTable($results):void
{
    $columns = array_keys($results[0]);  // Get Keys

    $str = '<table>';
    $str .= '<thead>';

    $str .= '<tr>';
    foreach($columns as $key)
    {
        $str .= '<th>' . $key . '</th>';
    }
    $str .= '</tr>';

    $str .= '</thead>';
    $str .= '<tbody>';

    foreach($results as $row)
    {
        $str .= '<tr>';
        foreach($row as $data)
        {
            $str .= '<td>' . $data . '</td>';
        }

        $str .= '<td>';
        $str .= '<a href="' . BASE_PATH . 'view/' . $row['id'] . '">View</a>';
        $str .= '</td>';

        $str .= '<td>';
        $str .= '<a href="' . BASE_PATH . 'edit/' . $row['id'] . '">Edit</a>';
        $str .= '</td>';

        $str .= '<td>';
        $str .= '<a href="' . BASE_PATH . 'delete/' . $row['id'] . '">Delete</a>';
        $str .= '</td>';

        $str .= '</tr>';
    }

    $str .= '</tbody>';
    $str .= '</table>';

    echo $str;
}

function GetRequestURI():array
{
    $request_uri = str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']);
    return array_values(array_filter(explode('/', $request_uri)));
}

function ApplyHead():void
{
    $head = '<!doctype html>
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bootstrap demo</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    </head>
    <body>';

    echo $head;
}

function ApplyFooter():void
{
    $footer = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" 
        crossorigin="anonymous"></script>
    </body>
    </html>';

    echo $footer;
}

?>