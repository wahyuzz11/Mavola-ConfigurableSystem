<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    public function users():BelongsToMany{
        return $this->belongsToMany(User::class,'menus_has_users','menus_id','users_id');
    }

    public function subMenus():HasMany{
        return $this->hasMany(SubMenu::class,'menus_id','id');
    }  



    

    
}
