<div>
        <h2>Создать пользователя</h2>
        <form action="{{ route('users.post') }}" method="post">
            @csrf
            <label for="login">Логин*</label> <br>
            <input type="text" placeholder="Введите логин" id="login" name="login" value="{{ old('login') }}"> <br>

            <label for="password">Пароль*</label> <br>
            <input type="text" placeholder="Введите пароль" id="password" name="password" value="{{ old('password') }}">
            <br>

            <label for="role">Роль</label> <br>
            <select name="role" id="role">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @if ($role->name == 'user') selected @endif>
                        {{ $role->description }}
                    </option>
                @endforeach
            </select><br>

            <button type="submit">Создать</button>
        </form>
    </div>
