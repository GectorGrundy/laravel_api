@extends('layouts.main')

@section('title'){{ $exception->getMessage() }}@endsection

@section('content')

	<div class="content">
		<h1 class="error_title">{{ $exception->getMessage() }}</h1>
	</div>

@endsection

@section('styles')
	<style>
		body {
			background-color: #f5f5f5;
			font-family: Georgia, serif;
		}

		.content {
			margin: 0 auto;
			max-width: 700px;
		}
		.error_title {
			margin-top: 25px;
			font-size: 20px;
		}
	</style>
@endsection