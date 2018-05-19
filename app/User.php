<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts() 
    {
        return $this->hasMany(Micropost::class);
    }
    
    
    // follow, unfollow
    public function followings() 
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 自分自身ではないかの確認
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me) {
            return false;
        } else {
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if ($exist && !$its_me) {
            $this->followings()->detach($userId);
            return true;
        } else {
            return false;
        }
    }
    
    public function is_following($userId) {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()-> pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
    
    // favorite
    // ユーザーのお気に入り一覧
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'user_favorite', 'user_id', 'favorite_id')->withTimestamps();
    }
    
    public function favorite($micropostId)
    {
        $exist = $this->is_favoriting($micropostId);
        $its_me = $this->id == Micropost::find($micropostId)->user_id;
        
        if ($exist || $its_me) {
            return false;
        } else {
            $this->favorites()->attach($micropostId);
            return true;
        }
    }
    
    public function unfavorite($micropostId)
    {
        $exist = $this->is_favoriting($micropostId);
        $its_me = $this->id == Micropost::find($micropostId)->user_id;
        
        if ($exist && !$its_me) {
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            return false;
        }
    }
    
    public function is_favoriting($micropostId) {
        return $this->favorites()->where('favorite_id', $micropostId)->exists();
    }
}
