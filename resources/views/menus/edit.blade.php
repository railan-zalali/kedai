@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold">Edit Menu</h1>
    <form method="POST" action="{{ route('menus.update', $menu) }}">
        @csrf
        @method('PUT')
        <input type="text" name="name" value="{{ $menu->name }}" class="border p-2">
        <select name="category_id" class="border p-2">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ $menu->category_id == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}</option>
            @endforeach
        </select>
        <input type="number" name="price" value="{{ $menu->price }}" class="border p-2">
        <textarea name="description" class="border p-2">{{ $menu->description }}</textarea>
        <button type="submit" class="bg-yellow-500 text-white p-2">Update</button>
    </form>
@endsection
