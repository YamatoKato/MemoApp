<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //ここでメモを取得する
        //memosでmemosテーブルの。*でそのテーブル内の全てのカラム取得,whereメソッドで取得条件つける
        $memos = Memo::select("memos.*")
            ->where("user_id","=",\Auth::id()) //ここではログインした人のidと一致したメモをとる
            ->whereNull("deleted_at") //deleted_atに日時が記録されてたら削除されたメモと扱う(論理削除)->Nullをつけることで削除以外のメモを取る
            ->orderBy("updated_at","DESC") //ASC = 小さい順 DESC=大きい順
            ->get();
            //dd($memos);

        $tags = Tag::where('user_id', '=', \Auth::id())->whereNull('deleted_at')->orderBy('id', 'DESC')->get();

        return view('create', compact('memos','tags'));
    }

    public function store(Request $request)   //Requestファザード内の関数を使えるようにしてる(インスタンス化)
    {
        $posts = $request->all();  //postされたデータを全てとる
        //$postsには　"content"=>"メモ内容"などが入ってる。

        // ===== ここからトランザクション開始 ======
        DB::transaction(function() use($posts) {
            // メモIDをインサートして取得
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id()]);
            $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])->exists(); 
            // 新規タグが入力されているかチェック
            // 新規タグが既にtagsテーブルに存在するのかチェック
            if( !empty($posts['new_tag']) && !$tag_exists ){
                // 新規タグが既に存在しなければ、tagsテーブルにインサート→IDを取得
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                // memo_tagsにインサートして、メモとタグを紐付ける
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }
            // 既存タグが紐付けられた場合→memo_tagsにインサート
            if(!empty($posts['tags'][0])){
                foreach($posts['tags'] as $tag){
                    MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag]);
                }
            }
        });
        // ===== ここまでがトランザクションの範囲 ======

        //メソッドの引数の取った値を展開して止める→データ確認
        //dd(\Auth::id());

        // 連想配列「キー => 値」 の形で記述する
        //$posts["content"]で内容を抽出
        //キーにカラム名,valueにメモ内容
        Memo::insert(["content" => $posts["content"], "user_id" => \Auth::id()]); //DBに入れる
        return redirect( route("home") );  //この書き方で/homeに戻れる
    }

    public function edit($id)
    {
        //消すとlayoutファイルにあるループ処理でエラーが出るから残す。
        //memosが無いのにmemosをループしたらエラーになる
        $memos = Memo::select("memos.*")
            ->where("user_id","=",\Auth::id()) //ここではログインした人のidと一致したメモをとる
            ->whereNull("deleted_at") //deleted_atに日時が記録されてたら削除されたメモと扱う(論理削除)->Nullをつけることで削除以外のメモを取る
            ->orderBy("updated_at","DESC") //ASC = 小さい順 DESC=大きい順
            ->get();
        
        $edit_memo = Memo::select('memos.*', 'tags.id AS tag_id')
            ->leftJoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
            ->leftJoin('tags', 'memo_tags.tag_id', '=', 'tags.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->where('memos.id', '=', $id)
            ->whereNull('memos.deleted_at')
            ->get();
            
        $include_tags = [];
        foreach($edit_memo as $memo){
            array_push($include_tags, $memo['tag_id']);
        }

        $tags = Tag::where('user_id', '=', \Auth::id())->whereNull('deleted_at')->orderBy('id', 'DESC')->get();

        return view('edit', compact('memos','edit_memo', 'include_tags', 'tags'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();

        //updateの前にwhereを入れることで指定されたmemo_idのみを更新する。
        Memo::where("id",$posts["memo_id"])->update(["content" => $posts["content"]]);
        return redirect( route("home") );  //この書き方で/homeに戻れる
    }

    public function destroy(Request $request)
    {
        $posts = $request->all();
        
        //delete()は物理削除だからNG
        //deleted_atにタイムスタンプを入れてやると論理削除できる
        //date()でタイムスタンプ生成
        Memo::where("id",$posts["memo_id"])->update(["deleted_at" => date("Y-m-d H:i:s",time())]);
        return redirect( route("home") );  //この書き方で/homeに戻れる
    }

}
