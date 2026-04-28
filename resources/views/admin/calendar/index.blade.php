@extends('layouts.main')

@section('header-title')
    Календарь
@endsection

@section('content')
    <div class="container-fluid">
        <div id="calendar"></div>
    </div>


    <!-- Модальное окно создания/редактирования задачи -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="taskForm" class="modal-content">
                @csrf
                <input type="hidden" id="task_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">Новая задача</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Название</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label>Описание</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Цвет</label>
                        <input type="color" class="form-control" id="color" name="color" value="#3788d8">
                    </div>
                    <div class="mb-3">
                        <label>Квота</label>
                        <input type="number" class="form-control" id="quota" name="quota" min="1">
                    </div>
                    <!-- Подразделение: выпадающие списки (dynamic) -->
                    <div class="mb-3">
                        <label>Подразделение</label>
                        <select class="form-select" id="commissariat_id" name="commissariat_id">
                            <option value="">Выберите комиссариат</option>
                            @foreach($commissariats as $com)
                                <option value="{{ $com->id }}">{{ $com->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Дата начала</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label>Дата окончания</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

@endsection