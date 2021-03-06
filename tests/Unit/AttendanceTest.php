<?php

namespace Tests\Unit;

use App\User;
use App\Member;
use Carbon\Carbon;
use Tests\TestCase;
use App\Attendance;

class AttendanceTest extends TestCase
{
    public function testOpenAttendancePage()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->get(route('attendance'));

        $response->assertStatus(200);
    }

    public function testStoreAttendance()
    {
        $user = factory(User::class)->create();

        $member1 = factory(Member::class)->create();
        $member2 = factory(Member::class)->create();

        $this->actingAs($user)
            ->post(route('storeAttendance'), [
                'date' => Carbon::parse('sunday')->toDateString(),
                'members' => [$member1->id, $member2->id]
            ]);
        $attendance = Attendance::where('date', Carbon::parse('sunday')->toDateString())->get();
        $this->assertEquals($member1->id,  $attendance[0]->member_id);
        $this->assertEquals($member2->id,  $attendance[1]->member_id);
    }

    public function testSummaryAttendance()
    {
        $user = factory(User::class)->create();

        $member1 = factory(Member::class)->create([
            'gender' => 'M',
        ]);
        $member2 = factory(Member::class)->create([
            'gender' => 'F',
        ]);
        $member3 = factory(Member::class)->create([
            'gender' => 'M',
        ]);

        $sunday = Carbon::parse('sunday');
        $this->actingAs($user)
            ->post(route('storeAttendance'), [
                'date' => $sunday->toDateString(),
                'members' => [$member1->id, $member2->id]
            ]);

        $sunday->subDay(7);
        $this->actingAs($user)
            ->post(route('storeAttendance'), [
                'date' => $sunday->toDateString(),
                'members' => [$member2->id]
            ]);

        $sunday->subDay(7);
        $this->actingAs($user)
            ->post(route('storeAttendance'), [
                'date' => $sunday->toDateString(),
                'members' => [$member1->id, $member2->id, $member3->id]
            ]);

        $summary = (new Attendance())->summary();
        $this->assertEquals(2, $summary[Carbon::parse('sunday')->toDateString()]['total']);
        $this->assertEquals(1, $summary[Carbon::parse('sunday')->toDateString()]['M']);
        $this->assertEquals(1, $summary[Carbon::parse('sunday')->subDay(7)->toDateString()]['total']);
        $this->assertEquals(1, $summary[Carbon::parse('sunday')->subDay(7)->toDateString()]['F']);
        $this->assertEquals(3, $summary[Carbon::parse('sunday')->subDay(14)->toDateString()]['total']);
        $this->assertEquals(2, $summary[Carbon::parse('sunday')->subDay(14)->toDateString()]['M']);
    }
}
