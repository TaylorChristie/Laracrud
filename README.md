# Laracrud
A simple to use CRUD extension to enable a programmer to quickly create CRUD structures in laravel. Very useful for administration panels.


#Usage in a Controller
```php
<?php
use \MineSQL\Laracrud\Crud as Crud;

class UserController extends Crud {
    public function __construct()
    {
        $restricted = ['email_address'];
        $private = ['password', 'remember_key'];
        parent::__construct(App\Models\User::class)->setReadonly($restricted)->setPrivate($private);
    }
  
    public function showAll()
    {
        $table = '<table>';
        $table .= $this->getAll(true); // gets all models as a table
        $table .= '</table>';
        
        return View::make('users/all')->withTable($table);
    }
  
    public function showOne($id)
    {
        $user = $this->getOne($id);
        return View::make('users/one')->withUser($user);
    }
  
    public function create()
    {
        if(Request::isMethod('POST')) {
            $id = $this->doCreate();
            // can set the user password here if needed
        }
        
        $formInputs = $this->showCreate(['email' => 'email']);
                      
        return View::make('users/new')->withInputs($formInputs);
    }
  
    public function update($id)
    {
        if(Request::isMethod('POST')) {
            $this->doUpdate($id);
        }
        
        $formInputs = $this->showUpdate($id, ['email' => 'email']);
        
        return View::make('users/update')->withInput($formInputs);
    }
  
    public function delete($id)
    {
        if(Request::isMethod('POST')) {
            $this->doDelete($id);
            return Redirect::to('/');
        }
        
        return View::make('users/delete')->withMessage('Are you sure you want to delete user #'.$id);
    }
}


```


#Usage in a Model
```php
<?php



```



#Usage standalone
```php


```
