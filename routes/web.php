<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetweetController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\HashtagController;


Auth::routes();


//related to Post

Route::get('/', [PostController::class, 'index'])->name('index');
Route::get('/posts/sort/{sort}', [PostController::class, 'index'])->name('posts.sort');
Route::get('search', [PostController::class, 'search'])->name('hashtag.search');
//---------------------------------------------------------------------------------------------------------------------


//related to Profile

Route::get('profile/{user}', [ProfileController::class, 'index'])->name('profile.index');
Route::get('posts', [ProfileController::class, 'create'])->middleware('auth')->name('post.create');
Route::post('posts', [ProfileController::class, 'store'])->middleware('auth')->name('post.store');
Route::get('posts/{post}/edit', [ProfileController::class,'edit'])->middleware('auth')->name('post.edit');
Route::put('posts/{post}/update', [ProfileController::class, 'update'])->middleware('auth')->name('post.update');
Route::delete('posts/{post}/delete', [ProfileController::class, 'destroy'])->middleware('auth')->name('post.destroy');
//---------------------------------------------------------------------------------------------------------------------



//related to Comment

Route::post('comments', [CommentController::class, 'store'])->middleware('auth')->name('posts.comments.store');
Route::get('posts/comments/{post}', [CommentController::class, 'show'])->middleware('auth')->name('posts.comments.show');
Route::delete('comments/{comment}/delete', [CommentController::class, 'destroy'])->middleware('auth')->name('comments.destroy');
//--------------------------------------------------------------------------------------------------------------------- 

//related to like/unlike

Route::post('/like-post/{id}',[LikeController::class,'likePost'])->middleware('auth')->name('like.post');
Route::post('/unlike-post/{id}',[LikeController::class,'unlikePost'])->middleware('auth')->name('unlike.post');

//related to Report
Route::post('/report-post/{post_id}', [ReportController::class,'reportpost'])->middleware('auth')->name('post.report');
//---------------------------------------------------------------------------------------------------------------------

//related to Retweet
Route::post('/posts/{post_id}/retweet', [RetweetController::class, 'retweet'])->middleware('auth')->name('retweet.store');
Route::delete('/retweets/{id}', [RetweetController::class, 'delete'])->middleware('auth')->name('retweet.delete');
//---------------------------------------------------------------------------------------------------------------------

//related to follow
Route::post('follow/{user}', [FollowerController::class, 'follow'])->middleware('auth')->name('follow');
Route::delete('unfollow/{user}', [FollowerController::class, 'unfollow'])->middleware('auth')->name('unfollow');
//---------------------------------------------------------------------------------------------------------------------

//related to user visibility
Route::post('users/{user}/visibility/update', [UserController::class, 'Visibility'])->middleware('auth')->name('users.visibility.update');

//---------------------------------------------------------------------------------------------------------------------




