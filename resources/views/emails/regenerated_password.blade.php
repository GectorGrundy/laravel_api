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
                {{ trans('emails.your_new_password', [], 'ru') }}: <b>{{ $new_password }}</b>
              </td>
            </tr>
          </table>
        </div>

      </td>
      <td></td>
    </tr>
  </table>
@endsection

