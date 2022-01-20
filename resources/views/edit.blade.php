@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        メモ編集
        <form class="card-body" action="{{ route('destroy') }}" method="POST">
            @csrf
            <input type="hidden" name="memo_id" value="{{$edit_memo[0]['id']}}"/>
            <button type="submit">削除</button>
        </form>
    </div>
    {{-- route('store')と書くと→ /store --}}
    <form class="card-body" action="{{ route('update') }}" method="POST">
        @csrf
        {{-- どのメモのidを更新するかを指定する --}}
        <input type="hidden" name="memo_id" value="{{$edit_memo[0]['id']}}"/>
        <div class="form-group">
            <textarea class="form-control" name="content" rows="3" placeholder="ここにメモを入力">{{$edit_memo[0]["content"]}}</textarea>
        </div>
    @foreach($tags as $t)
        <div class="form-check form-check-inline mb-3">
            {{-- 3項演算子 → if文を1行で書く方法 {{ 条件 ? trueだったら : falseだったら }}--}}
            {{-- もし$include_tagsにループで回っているタグのidが含まれれば、ckeckedを書く --}}
          <input class="form-check-input" type="checkbox" name="tags[]" id="{{ $t['id'] }}" value="{{ $t['id'] }}" {{ in_array($t['id'], $include_tags) ? 'checked' : '' }}>
          <label class="form-check-label" for="{{ $t['id'] }}">{{ $t['name']}}</label>
        </div>
    @endforeach

        <button type="submit" class="btn btn-primary">更新</button>
    </form>
</div>
@endsection
