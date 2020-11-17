<?php
namespace app\src;
/**
 * 
 */
abstract class DbModel extends Model
{
	function __construct()
	{
		# code...
	}

	abstract public function tableName();
	abstract public function attributes();
	abstract public function primaryKey();

	// Insert/Update record
	protected function save()
	{
		$tableName = $this->tableName();
		$attributes = $this->attributes();
		// If there is no id perform an insert
		if (!isset($this->id)) {
			$params = array_map(function($attr) {return ":$attr";}, $attributes);
			$sql = "INSERT INTO $tableName (".implode(',', $attributes).") VALUES (".implode(',', $params).")";
		}
		// Otherwise perform an update
		else {
			$params = array_map(function($attr) {return "$attr = :$attr";}, $attributes);
			$sql = "UPDATE $tableName SET ".implode(',', $params)." WHERE id = :id";
			$attributes[] = 'id';
		}

		$stmt = self::prepare($sql);
		foreach ($attributes as $attribute) {
			$stmt->bindValue(":$attribute", $this->{$attribute});
		}
		
		$stmt->execute();

		// Return true if record inserted/updated - false otherwise
		return ($stmt->rowCount() > 0);
	}

	// Takes id as parameter and deletes the record
	protected function delete($id)
	{
		$tableName = $this->tableName();
		$sql = "DELETE FROM $tableName WHERE id = :id";
		$stmt = self::prepare($sql);
		$stmt->bindValue(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();

    	// Return true if at least one record deleted - false otherwise
		return ($stmt->rowCount() > 0);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	* - Takes where condition attributes and columns to fetch as parameter
	* - Builds the query and executes prepared statement
	* - Fetches one record according to the return type (object/assoc/numeric) from DB
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function findOne($where, $returnType, $columns = [])
	{
		$tableName = static::tableName();
		$attributes = array_keys($where);
		$params = array_map(function($attr) {return "$attr = :$attr";}, $attributes);
		$columns = (!empty($columns)) ? implode(",", $columns) : '*';

		$sql = "SELECT $columns FROM $tableName WHERE " . implode("AND ", $params);
		$stmt = self::prepare($sql);
		
		foreach ($where as $key => $value) {
			$stmt->bindValue(":$key", $value);
		}

        $stmt->execute();
		if ($returnType === 'obj') {
			// Fetch result as object of called class type
			return $stmt->fetchObject(static::class) ?? null;
		}
		else {
			// Fetch result as associative array
			return $stmt->fetch() ?? null;
		}
        
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	* - Takes the query string and values to bind as parameter
	* - Builds the query and executes prepared statement
	* - Fetches all records according to the return type (object/assoc/numeric) from DB
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function findAll($sql, $returnType, $paramArray = [])
	{
		$stmt = self::prepare($sql);
		foreach ($paramArray as $key => $value) {
			$stmt->bindValue($key+1, $value);
		}
		$stmt->execute();
		if ($returnType === 'obj') {
			// Fetch all as an object array of called class type
			return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);
		}
		else {
			// Fetch all as an associative/numeric array
			return $stmt->fetchAll();
		}
	}

	protected function execute($sql, $paramArray = [])
	{
		$stmt = self::prepare($sql);
		foreach ($paramArray as $key => $value) {
			$stmt->bindValue($key+1, $value);
		}
		$stmt->execute();

		// Return true if at least one record affected - false otherwise
		return ($stmt->rowCount() > 0);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	* - Takes the query string and values to bind as parameter
	* - Returns the number of rows of the result
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	protected function getRecordCount($sql, $paramArray = [])
    {
		$stmt = self::prepare($sql);
		foreach ($paramArray as $key => $value) {
			$stmt->bindValue($key+1, $value);
		}
		
        $stmt->execute();
        $recordCount = $stmt->rowCount();
        return $recordCount;
    }

	public static function prepare($sql)
	{
		return Application::$app->dbc->prepare($sql);
	}

	// MySQLi prepared statements bind params
	private function bindQueryParams($stmt, $paramType, $paramArray = [])
	{
	    $paramValueReference[] = & $paramType;
	    for ($i = 0; $i < count($paramArray); $i ++) {
	        $paramValueReference[] = & $paramArray[$i];
	    }
	   call_user_func_array(array($stmt, 'bind_param'), $paramValueReference);
	}
}


