
<?php

function ConnectBdd($dbname = '', $user = 'root', $password = ''):PDO
{
    try
    {
        return new PDO('mysql:host=localhost;dbname=' . $dbname . ';charset=utf8', $user, $password);
    }
    catch (Exception $e)
    {
        die('Erreur: ' . $e->getMessage());
    }
}

function FetchAll($bdd, $table)
{
    $query = $bdd->prepare('SELECT * FROM ' . $table);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function FetchById($bdd, $table, $id)
{
    $query = $bdd->prepare('SELECT * FROM ' . $table . ' WHERE id = :id');
    $query->bindParam(':id', $id);
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}

function DeleteById($bdd, $table, $id)
{
    $query = $bdd->prepare('DELETE FROM ' . $table . ' WHERE id = :id');
    $query->bindParam(':id', $id);
    $query->execute();
}

function GetColumns($bdd, $table)
{
    $query = $bdd->prepare('SELECT * from information_schema.COLUMNS where TABLE_NAME = "' . $table . '"');
    $query->execute();
    return $query->FetchAll(PDO::FETCH_ASSOC);
}

function Save($bdd, $table, $data):void
{
    $keys = array_keys($data);

    $columns = implode(', ', $keys);
    $place_holder = ':' . implode(', :', $keys);

    $query = $bdd->prepare('INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $place_holder . ')');
    foreach($data as $key => $value)
    {
        $query->bindValue(':' . $key, $value);
    }
    $query->execute();
}

?>