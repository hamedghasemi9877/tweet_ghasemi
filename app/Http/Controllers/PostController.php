<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Hashtag;
use App\Models\Follower;
use Conner\Likeable\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller{

   

     public function index(User $user, $sort='newest') {

        $user = auth()->user();
        // just for users that authenticated-----------
        if($user){
          if($sort=='newest'){
              $posts = Post::withCount(['likes', 'comments'])
              ->orderBy('created_at', 'desc')
              ->get();
          }
          if($sort=='likeable'){
             $posts = Post::withCount(['likes', 'comments'])
              ->orderBy('likes_count', 'desc')
              ->get();
          }
          if($sort=='commentable'){
            $posts = Post::withCount(['likes', 'comments'])
             ->orderBy('comments_count', 'desc')
             ->get();
          }

        //  the posts of PrivateUsers------------
          $PostsPrivateUsers = Post::whereIn('user_id',function ($query){
            $query->select('id')->from('users')->where('visibility','private');
          })
        ->get();
                   // dd($PostsPrivateUsers);

        //   PublicUsers-------------
          $PublicUsers  = User::where('visibility','public' )->get();
          

       
        // implement users with same following_id------------
        $user_idsWithSameFollowing_id = Follower::selectRaw('GROUP_CONCAT(user_id) as user_ids')
        ->groupBy('following_id')
        ->havingRaw('COUNT(*) > 1')
        ->pluck('user_ids');
        $usersWithSameFollowingId = User::whereIn('id', $user_idsWithSameFollowing_id)->get();


        // user followings--------
        $following_id = $user->following->pluck('following_id');

 
        // the post of followings-----------
        
        $followingPosts = Post::whereIn('user_id', $following_id)->get();
         




        /*------------------------------
        if A follow B & B follow C
        suggestion to A that follow C

      
        so... 


        we need find folowings of user-----
        */
     

      //1: first find followings of user:
    $followingUserId = $user->following->pluck('following_id')->toArray();


      //suggestion tank
    $suggestions = [];

      //find the followings of the following
    foreach ($followingUserId as $followingId) {
        
      //all of the followings of userFollowings
        $followingfollowingUser = User::find($followingId)->following()->pluck('following_id')->toArray();

   
        //ids that exist in followingUserId but not exist in followersfollowingUser
        $suggestedUsers = array_diff($followingfollowingUser, $followingUserId);
    
        $suggestions = array_merge($suggestions, $suggestedUsers);
        
}

//no dublicate
$suggestions = array_unique($suggestions);

//remove owner user
$suggestions = array_diff($suggestions, [$user->id]);


$suggestedUsers = User::whereIn('id', $suggestions)->get();



        // Suggested Users to Follow with like system:
        // the ids of posts that user like them------------
        $postslikes_id = Like::where('user_id',$user->id)->pluck('likeable_id');
      

        //the user_id  of posts----------
        $likedOwners= Post::whereIn('id', $postslikes_id)->pluck('user_id');
         

        // owners---------
        $users1= User::whereIn('id',$likedOwners)->get();


        //  the best hashtags
        $topHashtags = DB::table('hashtag_post')
        ->select('hashtags.name', DB::raw('COUNT(*) as count'))
        ->join('hashtags', 'hashtag_post.hashtag_id', '=', 'hashtags.id')
        ->groupBy('hashtags.name')
        ->orderByDesc('count')
        ->limit(3)
        ->get();
// dd($topHashtagsWithNames);
        // ---
       
        // If the following user has a post
   if($followingPosts  ){
    
       return view('post.index', compact('posts','user','following_id','PostsPrivateUsers','PublicUsers','followingPosts','users1','usersWithSameFollowingId','suggestedUsers','topHashtags'));}
   
   
     else{// If the following user doesnt have a post
           
        
       return view('post.index', compact('posts','user','users','PublicUsers','users1','suggestedUsers','followingPosts','topHashtags'));
 }
        }
 
// if user not exists-----
 if($sort=='newest'){
     $posts = Post::withCount(['likes', 'comments'])
     ->orderBy('created_at', 'desc')
     ->paginate(10);
 }
 if($sort=='likeable'){
     $posts = Post::withCount(['likes', 'comments'])
    ->orderBy('likes_count', 'desc')
     ->paginate(10);
 }
 if($sort=='commentable'){
     $posts = Post::withCount(['likes', 'comments'])
     ->orderBy('comments_count', 'desc')
     ->paginate(10);
 }

 $users = User::where('visibility','public' )->get();
if($users){
  return view('post.index', compact('posts','users'));

}
// If the database was empty
return view('post.index');
         }
       
         
// ------------------------------------------------------------------
      

     
 public function search(Request $request){


    // validation
    $request->validate([
      'search' => 'required|string|max:10' 
  ]);

    $user = auth()->user();

  // give request
    $hashtag = $request->input('search');
    $hashtagExists = Hashtag::where('name',$hashtag)->exists();
     


                    // check
   if($hashtagExists){
    $hashtags2 = DB::table('hashtag_post')
    ->join('hashtags', 'hashtag_post.hashtag_id', '=', 'hashtags.id')
    ->where('hashtags.name', $hashtag)
    ->pluck('post_id');
  
  
     

      $posts = Post::whereIn('id', $hashtags2)
      ->whereIn('user_id', function ($query) use ($hashtags2) {
          $query->select('user_id')
              ->from('posts')
              ->whereIn('id', $hashtags2);
      })
      ->whereHas('user', function ($query) {
          // Filter for public users
          $query->where('visibility', 'public');
      })
      
     ->orWhere('user_id', function ($query) {
         // Filter for users being followed
         $query->select('following_id')
             ->from('followers')
             ->where('user_id', auth()->id());
     })
     ->get();
         
    //  dd($posts);
    return view('post.search',compact('posts','user'));

   }
   else{
    return redirect()->back()->with('error','No posts found for the hashtag');
   
  }

}
}