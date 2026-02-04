<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;


class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required','string','max:180'],
            'description' => ['nullable','string'],

            'day_of_week' => ['nullable','integer','between:1,7'],
            'starts_time' => ['nullable','date_format:H:i'],
            'ends_time'   => ['nullable','date_format:H:i'],

            'capacity'    => ['nullable','integer','min:1'],
            'teacher_id'  => ['nullable','integer','exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $dow    = $this->input('day_of_week');
            $starts = $this->input('starts_time');
            $ends   = $this->input('ends_time');

            // If any schedule field exists, we need to require all.
            $anySchedule = ($dow !== null) || ($starts !== null) || ($ends !== null);

            if ($anySchedule) {
                if (!$dow)    $v->errors()->add('day_of_week', 'day_of_week is required when schedule is provided.');
                if (!$starts) $v->errors()->add('starts_time', 'starts_time is required when schedule is provided.');
                if (!$ends)   $v->errors()->add('ends_time', 'ends_time is required when schedule is provided.');
            }

            if ($starts && $ends && $ends <= $starts) {
                $v->errors()->add('ends_time', 'End time must be after start time.');
            }
        });
    }
}