<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\AttendanceType;
use App\Models\UserDefaultSchedule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'title',
        'cfp',
        'birth_date',
        'mobile_number',
        'phone_number',
        'category',
        'weekly_hours',
        'badge_code',
        'company_name',
        'vat_number',
        'employee_code',
        'address',
        'city',
        'street_number',
        'postal_code',
        'province',
        'latitude',
        'longitude',
        'location_address',
        'location_city',
        'location_street_number',
        'location_postal_code',
        'location_province',
        'location_latitude',
        'location_longitude',
        'color',
    ];

    private $colorBank = [
        '#7C9EB2',
        '#9E788F',
        '#F39C6B',
        '#35B293',
        '#7CEA9C',
        '#BC427F',
    ];

    public function assignColorToUser()
    {
        $this->color = $this->colorBank[array_rand($this->colorBank)];
        $this->save();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function timeOffRequests()
    {
        return $this->hasMany(TimeOffRequest::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'groups_users', 'user_id', 'group_id');
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'user_vehicle', 'user_id', 'vehicle_id')
            ->withPivot([
                'vehicle_type',
                'plate_number',
                'ownership_type',
                'purchase_type',
                'contract_start_date',
                'contract_end_date',
                'mileage',
                'mileage_update_date',
            ]);
    }

    public function mileageUpdates()
    {
        return $this->hasMany(MileageUpdate::class);
    }

    public function failedAttendances()
    {
        return $this->hasMany(FailedAttendance::class);
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function defaultSchedules()
    {
        return $this->hasMany(UserDefaultSchedule::class);
    }

    public function ensureDefaultSchedule()
    {
        if ($this->defaultSchedules()->exists()) {
            return $this->defaultSchedules;
        }

        $entries = [];
        $workDays = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        $defaultAttendanceTypeId = AttendanceType::where('acronym', 'LS')->value('id')
            ?? AttendanceType::query()->value('id');

        foreach ($workDays as $day) {
            $entries[] = [
                'day' => $day,
                'hour_start' => '08:00',
                'hour_end' => '12:00',
                'total_hours' => 4,
                'attendance_type_id' => $defaultAttendanceTypeId,
            ];

            $entries[] = [
                'day' => $day,
                'hour_start' => '13:00',
                'hour_end' => '17:00',
                'total_hours' => 4,
                'attendance_type_id' => $defaultAttendanceTypeId,
            ];
        }

        return $this->defaultSchedules()->createMany($entries);
    }

    public function schedules()
    {
        return $this->hasMany(UserSchedule::class);
    }

    public function scheduleChangeRequests()
    {
        return $this->hasMany(UserScheduleChangeRequest::class);
    }

    public function viewedAnnouncements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user')->withTimestamps();
    }

    public function unreadAnnouncements()
    {
        return Announcement::active()
            ->whereNotIn('id', $this->viewedAnnouncements()->pluck('id'))
            ->orderBy('created_at', 'desc');
    }
}
