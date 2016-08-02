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
	
	
	// processes input from a showCreate form, 
	public function doCreate()
	{
		$model = new $this->model();
		$props = \Schema::getColumnListing($model);
		
		foreach(Input::all() as $key => $value)
		{
			if(in_array($key, $props))
			{
				$model->$key = $value;
			}
		}
		
		$model->save();
		
		return $model->id;
	
	}
	
	// I want this form to be somewhat intelligent and define the inputs properly (numbers, switches, passwords, ect)
	public function showCreate()
	{
	
	
	}
	
	public function doUpdate()
	{
	
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


}
