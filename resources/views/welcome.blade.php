@if(session()->has('message'))
    <p class="text-success">{{session('message')}}</p>
@endif
Hello!

<a href="{{route('contacts.create')}}" class="btn btn-outline-primary waves-effect">Новый контакт</a>
