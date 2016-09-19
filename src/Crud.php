<?php
namespace MineSQL\Laracrud;

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
abstract class Crud extends \BaseController
{
    /**
     * Elements that can not be overwritten by the user in a request.
     *
     * @var array
     */
    private $readOnly = ['id', 'created_at', 'updated_at']; // default 

    /**
     * Elements that cannot be read or edited by the user.
     *
     * @var array
     */
    private $private = [];

    /**
     * Model instance to handle builtin Model:: methods.
     *
     * @var mixed
     */
    private $model;

    /**
     * Table Name for Schema:: operations
     *
     * @var mixed
     */
    private $modelName;

    /**
     * Construct a new CRUD handler. Note: this method should always be overridden, then parent::__construct() called in
     * the extending class. ie:
     *
     * ```php
     * parent::__construct(App\Models\MyModel::class);
     * ```
     *
     * @param string $model the absolute class name of the Model to perform CRUD operations on. (and to be initalized)
     * @throws Exception
     */
    public function __construct($model)
    {
        $this->model = new $model();
        $this->modelName = $this->model->getTable();
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
     * @param mixed $asTable optional output as a table instead of a laravel collection
     * @param string $editUrl the laravel URL that directs to an edit page ie: users/edit --> users/edit/4 where 4 = user id
     * @param string $deleteUrl same as the edit url, except it is to the delete route
     * @return mixed
     */
    public function getAll($asTable = 0, $editUrl, $deleteUrl) 
    {
        $props = $this->getProps();
       

        $i = $this->model;
        $data = $i::all($props);

         $props[] = 'actions';


        if($asTable) {
            $html = '';
            
            $html .= '<thead><tr>';

            foreach($props as $prop) {

                $html .= '<th>'.ucwords(str_replace('_', ' ', $prop)).'</th>';
            }
            $html .= '</tr></thead><tbody>';
        
            foreach($data as $row) {
                $row['action'] = '<a href="'.\URL::to($editUrl.'/'.$row['attributes']['id']).'" class="btn btn-warning">Edit</a> <a href="'.\URL::to($deleteUrl).'/'.$row['attributes']['id'].'" onClick="return confirm(\'Are you sure you want to delete this record permmanently?\')" class="btn btn-danger">Delete</a>';
                $html .= '<tr>';
                foreach($row['attributes'] as $value) {
                    $html .= '<td>'.$value.'</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            
            return $html;
        }
        
        return $data;
        
    }

    /**
     * get one of the elements to the user.
     *
     * @param mixed $id id of the element to read
     * @return mixed
     */
    public function getOne($id)
    {
        $i = $this->model;

        $info = $i::select($this->getProps())->findOrFail($id);
        
        return $info;
    }

    /**
     * Processes input from a showCreate form.
     *
     * @return Model
     */
    public function doCreate()
    {
        $i = $this->model;

        return $this->updateFromInput($i);
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
     * @param mixed $inputClass   classes to apply to each input
     * @param mixed $btnClass     class the button should have
     * @return array array of inputs
     */
    public function showCreate(
        array $specialTypes = [],
        $inputClass = 'form-control',
        $btnClass = 'btn btn-primary'
    ) {
        $props = $this->getProps();

        foreach ($props as $prop) {
            if (!in_array($prop, $this->readOnly) || !in_array($prop, $this->private)) {

                $type = isset($specialTypes[$prop]) ? $specialTypes[$prop] : '';

                $formInput[] = "<label>{$prop}</label><input type='{$type}' name='{$prop}' id='input-{$prop}' class='{$inputClass}'>";
            }
        }

        $formInput[] = "<input type='submit' class='{$btnClass}' value='Create'>";

        return $formInput;
    }

    /**
     * Update record `$id` with values passed by the user in the input array.
     *
     * @param mixed $id record to update
     * @return mixed
     */
    public function doUpdate($id)
    {
        $i = $this->model;

        return $this->updateFromInput($i::findOrFail($id));
    }

       /**
     * Generate form inputs for a row to be updated. Use `$specialTypes` to map a property to a input type, ie:
     *
     * ```php
     * 'id' => 'number',
     * 'email_address' => 'email'
     * ```
     * @param mixed $id the id of the row to be updated
     * @param array  $specialTypes map database field to input type
     * @param mixed $inputClass   classes to apply to each input
     * @param mixed $btnClass     class the button should have
     * @return array array of inputs
     */
    public function showUpdate(
        $id,
        array $specialTypes = [],
        $inputClass = 'form-control',
        $btnClass = 'btn btn-primary'
    ) {
        $i = $this->model;

        $row = $i::findOrFail($id);

        $props = $this->getProps();

        foreach ($props as $prop) {
            
            if (!in_array($prop, $this->readOnly)) {
                
                $value = $row->$prop;
                $type = isset($specialTypes[$prop]) ? $specialTypes[$prop] : '';
                $formInput[] = "<label>{$prop}</label><input type='{$type}' name='{$prop}' id='input-{$prop}' value='{$value}' class='{$inputClass}'>";
            }
        }

        $formInput[] = "<input type='submit' class='{$btnClass}' value='Update'>";

        return $formInput;
    }

    /**
     * Delete a value from the database with id `$id`.
     *
     * @param mixed $id id of the record to delete
     * @return mixed
     */
    public function doDelete($id)
    {
        $i = $this->model;

        return $i::findOrFail($id)->delete();
    }

    /**
     * Get properties this user can edit (or view) from the database.
     *
     * @return array
     */
    private function getProps()
    {
        return array_diff(Schema::getColumnListing($this->modelName), $this->private);
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
