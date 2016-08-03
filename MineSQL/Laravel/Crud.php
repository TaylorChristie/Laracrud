<?php
namespace \MineSQL\Laravel;

// I want this file to be extendable by a controller/model so you can easily use it in your application with little setup

class Crud
{

	public $readOnly = ['id', 'created_at', 'updated_at']; 

	private $model;


	public function __construct(string $model)
	{
		$this->model = $model;
	}
	
	
	public function setProtected(array $protected)
	{
		foreach($protected as $one)
		{
			$this->readOnly[] = $one;
		}
		
		return true;
	}
	
	public function showAll()
	{
		
	}
	
	public function showOne($id)
	{
		
	}
	
	// processes input from a showCreate form, 
	public function doCreate()
	{
		$model = new $this->model();
		
		return $this->updateFromInput($model);
	
	}
	
	// I want this form to be somewhat intelligent and define the inputs properly (numbers, switches, passwords, ect)
	public function showCreate($specialTypes= [], $inputClass = 'form-control', $btnClass = 'btn btn-primary')
	{
		$props = $this->getProps();
		
		foreach($props as $prop)
		{
			if(!in_array($prop, $this->readOnly))
			{
				(array_key_exists($prop, $specialTypes)) ? $type = $specialTypes[$prop] : $type = "";
				
				$formInput[] = "<input type='{$type}' name='{$prop}' id='{$prop}' class='{$inputClass}' />";
			}
		}
		
		$formInput[] = "<input type='submit' class='{$btnClass}' value='Create' />";

		return $formInput;
	
	}
	
	public function doUpdate($id)
	{
		$model = $this->model->find($id);
		
		return $this->updateFromInput($model);
	}
	
	public function showUpdate()
	{
	
	}
	
	public function doDelete()
	{
	
	}
	
	public function showDelete()
	{
	
	}
	
	private function getProps()
	{
		$model = new $this->model();
		$props = \Schema::getColumnListing($model);
		
		return $props;
	}
	
	private function updateFromInput($modelInstance)
	{
		$props = $this->getProps();
		
		foreach(Input::all() as $key => $value)
		{
			if(in_array($key, $props) && !in_array($key, $this->readOnly)) // needs to be in the database column, and can't be read only
			{
				$modelInstance->$key = $value;
			}
		}
		
		$modelInstance->save();
		
		return $modelInstance;
	}


}
