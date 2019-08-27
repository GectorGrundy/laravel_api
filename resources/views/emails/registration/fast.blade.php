@extends('emails.main')

@section('main')
	<table class="body-wrap" bgcolor="#f7f9fa">
		<tr>
			<td></td>
			<td class="container" bgcolor="#FFFFFF">

				<div class="content">
					<table>
						<tr>
							<td>
								<strong>{{ trans('publishers.registration_fast_title') }}</strong>
								<br>
								{{ trans('publishers.registration_you_password') }}: {{ $inputs['password'] }}
								<br>
							</td>
						</tr>
					</table>
				</div>

			</td>
			<td></td>
		</tr>
	</table>
@endsection
