<?php
namespace MineSQL\Laravel;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;

/**
 * Abstract class that can be extended by both controllers and models to easily use in your application with as little
 * effort as possible.
 *
 * @author MineSQL
 * @author ASDA
 */
abstract class Crud
{
    /**
     * Elements that can not be overwritten by the user in a request.
     *
     * @var array
     */
    private $readOnly = ['id', 'created_at', 'updated_at'];

    /**
     * Elements that cannot be read or edited by the user.
     *
     * @var array
     */
    private $private = [];

    /**
     * Model to handle CRUD operations for.
     *
     * @var mixed
     */
    private $model;

    /**
     * Construct a new CRUD handler. Note: this method should always be overridden, then parent::__construct() called in
     * the extending class. ie:
     *
     * ```php
     * parent::__construct(App\Models\MyModel::class);
     * ```
     *
     * @param mixed $model model to perform CRUD operations on.
     * @throws Exception
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Set the protected elements that can not be edited by the user.
     *
     * @param array $protected values to make read only
     * @return $this
     */
    public function setProtected(array $protected)
    {
        $this->readOnly = array_unique(array_merge($this->readOnly, $protected));

        return $this;
    }

    /**
     * Set the private elements that cannot be either read or written to by the user.
     *
     * @param array $private values to make hidden from the user
     * @return $this
     */
    public function setPrivate(array $private)
    {
        $this->private = array_unique(array_merge($this->private, $private));

        return $this;
    }

    /**
     * get all the elements to the user, optionally show it as a table.
     *
     * 
     * @param boolean $asTable optional output as a table instead of a laravel collection
     * @return mixed
     */
    public function getAll(boolean $asTable = false) 
    {
        $data = ($this->model)::all();
    
        if($asTable) {
            $html = '';
            
            foreach($this->getProps() as $prop) {
                $html .= '<th>';
                
                foreach($prop as $one) {
                    $html .= '<td>'.$one.'</td>';
                }
                $html .= '</th>';
            }
        
            foreach($data as $row) {
                $html .= '<tr>';
                foreach($row as $value) {
                    $html .= '<td>'.$value.'</td>';
                }
                $html .= '</tr>';
            }
            
            return $html;
        }
        
        return $data;
        
    }

    /**
     * get one of the elements to the user.
     *
     * @param int $id id of the element to read
     * @return mixed
     */
    public function getOne(int $id)
    {
        $info = ($this->model)::findOrFail($id);
        
        return $info;
    }

    /**
     * Processes input from a showCreate form.
     *
     * @return Model
     */
    public function doCreate()
    {
        $model = new $this->model;

        return $this->updateFromInput($model);
    }

    /**
     * Generate form inputs for this model. Use `$specialTypes` to map a property to a input type, ie:
     *
     * ```php
     * 'id' => 'number',
     * 'email_address' => 'email'
     * ```
     *
     * @param array  $specialTypes map database field to input type
     * @param string $inputClass   classes to apply to each input
     * @param string $btnClass     class the button should have
     * @return array array of inputs
     */
    public function showCreate(
        array $specialTypes = [],
        string $inputClass = 'form-control',
        string $btnClass = 'btn btn-primary'
    ) {
        $props = $this->getProps();

        foreach ($props as $prop) {
            if (!in_array($prop, $this->readOnly)) {
                $type = $specialTypes[$prop] ?: '';
                $formInput[] = "<input type='{$type}' name='{$prop}' id='input-{$prop}' class='{$inputClass}'>";
            }
        }

        $formInput[] = "<input type='submit' class='{$btnClass}' value='Create'>";

        return $formInput;
    }

    /**
     * Update record `$id` with values passed by the user in the input array.
     *
     * @param int $id record to update
     * @return mixed
     */
    public function doUpdate(int $id)
    {
        return $this->updateFromInput(($this->model)::findOrFail($id));
    }

       /**
     * Generate form inputs for a row to be updated. Use `$specialTypes` to map a property to a input type, ie:
     *
     * ```php
     * 'id' => 'number',
     * 'email_address' => 'email'
     * ```
     * @param integer $id the id of the row to be updated
     * @param array  $specialTypes map database field to input type
     * @param string $inputClass   classes to apply to each input
     * @param string $btnClass     class the button should have
     * @return array array of inputs
     */
    public function showUpdate(
        int $id,
        array $specialTypes = [],
        string $inputClass = 'form-control',
        string $btnClass = 'btn btn-primary'
    ) {
        $row = ($this->model)::findOrFail($id);
        $props = $this->getProps();

        foreach ($props as $prop) {
            
            if (!in_array($prop, $this->readOnly)) {
                
                $value = $row->$prop;
                $type = $specialTypes[$prop] ?? '';
                $formInput[] = "<input type='{$type}' name='{$prop}' id='input-{$prop}' value='{$value}' class='{$inputClass}'>";
            }
        }

        $formInput[] = "<input type='submit' class='{$btnClass}' value='Update'>";

        return $formInput;
    }

    /**
     * Delete a value from the database with id `$id`.
     *
     * @param int $id id of the record to delete
     * @return mixed
     */
    public function doDelete(int $id)
    {
        return ($this->model)::findOrFail($id)->delete();
    }

    /**
     * Get properties this user can edit (or view) from the database.
     *
     * @return array
     */
    private function getProps()
    {
        return array_diff(Schema::getColumnListing(new $this->model), $this->private);
    }

    /**
     * Update values in the database using input values.
     *
     * @param Model $modelInstance record to update values for.
     * @return mixed
     */
    private function updateFromInput(Model $modelInstance)
    {
        $props = $this->getProps();

        foreach (Input::all() as $key => $value) {
            if (in_array($key, $props) && !in_array($key, $this->readOnly) && !in_array($key, $this->private)) {
                // needs to be in the database column, and can't be read only, and can't be private
                $modelInstance->$key = $value;
            }
        }

        $modelInstance->save();

        return $modelInstance;
    }
}
