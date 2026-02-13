<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes','string','max:180'],
            'description' => ['sometimes','nullable','string'],

            'type'        => ['sometimes','string','in:weekly,workshop'],
            'active'      => ['sometimes','boolean'],

            'day_of_week' => ['sometimes','nullable','integer','between:1,7'],
            'starts_time' => ['sometimes','nullable','date_format:H:i'],
            'ends_time'   => ['sometimes','nullable','date_format:H:i'],

            'capacity'      => ['sometimes','nullable','integer','min:1'],
            'monthly_price' => ['sometimes','numeric','min:0','max:9999.99'],
            'teacher_id'  => ['sometimes','nullable','integer','exists:users,id'],

            // Workshop sessions
            'sessions'               => ['sometimes','nullable','array','min:1'],
            'sessions.*.date'        => ['required_with:sessions','date'],
            'sessions.*.starts_time' => ['required_with:sessions','date_format:H:i'],
            'sessions.*.ends_time'   => ['required_with:sessions','date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Only validate time order if BOTH keys are present in request.
            if ($this->has('starts_time') && $this->has('ends_time')) {
                $starts = $this->input('starts_time');
                $ends   = $this->input('ends_time');

                if ($starts && $ends && $ends <= $starts) {
                    $v->errors()->add('ends_time', 'End time must be after start time.');
                }
            }

            // Validate sessions if provided
            if ($this->has('sessions') && is_array($this->input('sessions'))) {
                $sessions = $this->input('sessions');

                foreach ($sessions as $i => $session) {
                    $sStart = $session['starts_time'] ?? null;
                    $sEnd   = $session['ends_time'] ?? null;
                    if ($sStart && $sEnd && $sEnd <= $sStart) {
                        $v->errors()->add("sessions.$i.ends_time", 'Session end time must be after start time.');
                    }
                }

                // Unique dates
                $dates = array_column($sessions, 'date');
                if (count($dates) !== count(array_unique($dates))) {
                    $v->errors()->add('sessions', 'Session dates must be unique.');
                }
            }
        });
    }
}
