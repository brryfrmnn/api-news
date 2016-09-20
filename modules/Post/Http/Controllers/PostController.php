<?php

namespace Modules\Post\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Modules\Post\Model\Post;

class PostController extends Controller
{
    use Helpers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'q' => 'string',
            'result_type' => 'string',
            'offset' => 'integer',
            'category'=>'string'
        ]);
        if ($validator->passes()) {
            $q              = $request->q;
            $result_type    = $request->result_type;
            $offset         = $request->input('offset',10);
            $category       = $request->category;
            $filter         = $request->filter;
            
            $post = Post::filter($filter);
            if ($q!=null) {
                $post->search($q,$result_type);
            }
            if ($category != null) {
                $post->byCategory($category);
            }
            $post = $post->paginate($offset)->appends($request->input());
            $meta['status'] = true;
            $meta['message'] = "List All Post";
            $meta['total'] = $post->total();
            $meta['offset'] = $post->perPage();
            $meta['current'] =$post->currentpage();
            $meta['last']=$post->lastPage();
            $meta['next']=$post->nextPageUrl();
            $meta['prev']=$post->previousPageUrl();
            $data = $post->all();    
        }
        else
        {
            $meta['status'] = false;
            $meta['message'] = "Failed";
            $meta['error'] = $validator->errors();
            $data = null;
        }
        
        $meta['code'] = 200;
        $code = 200;
        return $this->response->array(compact('meta','data'))->setStatusCode($code);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
                'title' => 'required|string',
                'category_id' => 'integer',
                'image' => 'image',
                'article' => 'required|string|min:300',
                'writer_id' => 'required|integer',
                'admin_id' => 'integer',
                'editor_id' => 'integer'
        ]);
        if ($validator->passes()) {
            $title      = $request->title;
            $category_id= $request->category_id;
            $article    = $request->article;
            $writer_id  = $request->writer_id;
            $admin_id   = $request->admin_id;
            $status     = 0;
            $image      = $request->file('image');
            
            try 
            {
                //upload image to storage
                $extension  = $image->getClientOriginalExtension();
                $fileName      = str_replace(' ', '_', $title).uniqid();
                $mergeFileName = $fileName.'.'.$extension;
                $destination   = storage_path('app/public/');
                if ($image!= null) {
                    $image = \Storage::put(
                        'public/'.$mergeFileName,
                        file_get_contents($request->file('image')->getRealPath())
                    );
                    //resize image
                    $resize = \Image::make(asset('storage/'.$mergeFileName));
                    $resize->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destination . $fileName . '_resize800.' . $extension);
                    //crop image
                    $resize->fit(500, 500)->save($destination . $fileName . '_square500.' . $extension);
                }
                
                $post = new Post;
                $post->title        = $title;
                $post->category_id  = $category_id;
                $post->article      =  $article;
                $post->status       = 0;
                if (isset($mergeFileName)) {
                    $post->image    = $mergeFileName;   
                }
                $post->writer_id    = $writer_id;
                if ($post->save()) {
                    $meta['status'] = true;
                    $meta['message'] = "Success creating post";    
                }
                
            } 
            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) 
            {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }
            catch (\Illuminate\Database\QueryException $e) {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }

        }
        else
        {
            $meta['status'] = false;
            $meta['message'] = "Failed";
            $meta['error'] = $validator->errors();
            $data = null;
        }
        
        $meta['code'] = 200;
        $code = 200;
        return $this->response->array(compact('meta','data'))->setStatusCode($code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
                'id'       => 'required',
        ]);
        if ($validator->passes()) {
            $id        = $request->id;

            try 
            {
                if (is_numeric($id)) {
                    $post = Post::findOrFail($id);
                    $meta['status'] = true;
                    $meta['message'] = "Success showing post ID #".$id;
                    $data = $post;       
                } else if (is_string($id)) {
                    $post = Post::findBySlug($id);
                    $meta['status'] = true;
                    $meta['message'] = "Success showing post";
                    $data = $post;
                }                 
            } 
            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) 
            {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }
            catch (\Illuminate\Database\QueryException $e) {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }

        }
        else
        {
            $meta['status'] = false;
            $meta['message'] = "Failed";
            $meta['error'] = $validator->errors();
            $data = null;
        }
        
        $meta['code'] = 200;
        $code = 200;
        return $this->response->array(compact('meta','data'))->setStatusCode($code);
    
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = \Validator::make($request->all(), [
                'id' => 'required|integer',
                'title' => 'required|string',
                'category_id' => 'integer',
                'image' => 'image',
                'article' => 'required|string|min:300',
                'writer_id' => 'required|integer',
                'admin_id' => 'integer',
                'editor_id' => 'integer'
        ]);
        if ($validator->passes()) {
            $id         = $request->id;
            $title      = $request->title;
            $category_id= $request->category_id;
            $article    = $request->article;
            $writer_id  = $request->writer_id;
            $admin_id   = $request->admin_id;
            $status     = $request->status;
            $image      = $request->file('image');
            
            try 
            {
                //upload image to storage
                $extension  = $image->getClientOriginalExtension();
                $fileName      = str_replace(' ', '_', $title).uniqid();
                $mergeFileName = $fileName.'.'.$extension;
                $destination   = storage_path('app/public/');
                if ($image != null) {
                    $image = \Storage::put(
                        'public/'.$mergeFileName,
                        file_get_contents($request->file('image')->getRealPath())
                    );
                    //resize image
                    $resize = \Image::make(asset('storage/'.$mergeFileName));
                    $resize->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destination . $fileName . '_resize800.' . $extension);
                    //crop image
                    $resize->fit(500, 500)->save($destination . $fileName . '_square500.' . $extension);
                }

                $post = Post::findOrFail($id);
                $post->title        = $title;
                $post->category_id  = $category_id;
                $post->article      = $article;
                $post->status       = 0;
                if (isset($mergeFileName)) {
                    $post->image    = $mergeFileName;   
                }
                $post->writer_id    = $writer_id;
                if ($post->save()) {
                    $meta['status'] = true;
                    $meta['message'] = "Success updating post";    
                }
                
            } 
            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) 
            {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }
            catch (\Illuminate\Database\QueryException $e) {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }

        }
        else
        {
            $meta['status'] = false;
            $meta['message'] = "Failed";
            $meta['error'] = $validator->errors();
            $data = null;
        }
        
        $meta['code'] = 200;
        $code = 200;
        return $this->response->array(compact('meta','data'))->setStatusCode($code);
    }

    public function updateStatus(Request $request)
    {
        $validator = \Validator::make($request->all(), [
                'id' => 'required|integer',
                'admin_id' => 'integer',
                'editor_id' => 'required|integer|exists:users,id',
                'status' => 'required|integer'
        ]);
        if ($validator->passes()) {
            $id = $request->id;
            $admin_id = $request->admin_id;
            $editor_id = $request->editor_id;
            $status   = $request->status;
            try 
            {
                $post = Post::findOrFail($id);
                $post->status = $status;
                if ($post->save()) {
                    $meta['status'] = true;
                    $meta['message'] = 'Success Update status';
                    $data  = $post;
                }
            } 
            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) 
            {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }
            catch (\Illuminate\Database\QueryException $e) {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }
            
        }
        else
        {
            $meta['status'] = false;
            $meta['message'] = "Failed";
            $meta['error'] = $validator->errors();
            $data = null;
        }
        
        $meta['code'] = 200;
        $code = 200;
        return $this->response->array(compact('meta','data'))->setStatusCode($code);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validator = \Validator::make($request->all(), [
                'id' => 'required|integer',
                'admin_id' => 'integer',
                'writer_id' => 'integer'
        ]);
        if ($validator->passes()) {
            $id       = $request->id;
            $admin_id = $request->admin_id;
            $writer_id= $request->writer_id;

            try 
            {
                $post = Post::findOrFail($id);
                if ($writer_id == $post->writer_id) {
                    if ($post->delete()) {
                        $meta['status'] = true;
                        $meta['message'] = "Success deleting post"; 
                    }
                } else {
                    $meta['status'] = true;
                    $meta['message'] = "access denied";
                }
            } 
            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) 
            {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }
            catch (\Illuminate\Database\QueryException $e) {
                $meta['status'] = false;
                $meta['message'] = 'Error '.$e;
            }
            
        }
        else
        {
            $meta['status'] = false;
            $meta['message'] = "Failed delete post";
            $meta['error'] = $validator->errors();
            $data = null;
        }
        
        $meta['code'] = 200;
        $code = 200;
        return $this->response->array(compact('meta','data'))->setStatusCode($code);
    }
}