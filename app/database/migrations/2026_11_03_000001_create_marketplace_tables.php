<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('customer')->index(); // admin|business|customer
            $t->string('phone')->nullable();
            $t->string('provider')->nullable();     // google|facebook
            $t->string('provider_id')->nullable();
            $t->string('password')->nullable()->change();
            $t->unique(['provider', 'provider_id']);
        });

        Schema::create('businesses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('category')->index();
            $t->text('description')->nullable();
            $t->string('city')->index();
            $t->string('address');
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->string('phone');
            $t->string('email')->nullable();
            $t->string('website')->nullable();
            $t->string('facebook_url')->nullable();
            $t->string('instagram_url')->nullable();
            $t->string('tiktok_url')->nullable();
            $t->string('google_listing_url')->nullable();
            $t->text('google_reviews_embed')->nullable();      // pasted by owner
            $t->boolean('embed_approved')->default(false);     // approved by admin
            $t->string('status')->default('pending')->index(); // pending|approved|rejected
            $t->timestamp('trial_ends_at')->nullable();
            $t->timestamps();
        });

        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('duration_minutes');
            $t->decimal('price_eur', 8, 2)->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('staff', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('google_calendar_id')->nullable(); // calendar shared with our service account
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('service_staff', function (Blueprint $t) {
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $t->primary(['service_id', 'staff_id']);
        });

        Schema::create('working_hours', function (Blueprint $t) {
            $t->id();
            $t->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $t->unsignedTinyInteger('weekday'); // 0=Mon .. 6=Sun
            $t->time('start_time');
            $t->time('end_time');
        });

        Schema::create('appointments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('service_id')->constrained();
            $t->foreignId('staff_id')->constrained('staff');
            $t->foreignId('user_id')->constrained();
            $t->dateTime('starts_at')->index();
            $t->dateTime('ends_at');
            $t->string('status')->default('pending'); // pending|confirmed|cancelled|completed
            $t->string('google_event_id')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('reminder_morning_sent_at')->nullable();
            $t->timestamp('reminder_2h_sent_at')->nullable();
            $t->timestamps();
        });

        Schema::create('photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained();
            $t->string('path');
            $t->string('caption')->nullable();
            $t->string('status')->default('pending');
            $t->timestamps();
        });

        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained();
            $t->unsignedTinyInteger('rating');
            $t->text('body')->nullable();
            $t->text('owner_reply')->nullable();
            $t->string('status')->default('pending');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['reviews', 'photos', 'appointments', 'working_hours', 'service_staff', 'staff', 'services', 'businesses'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone', 'provider', 'provider_id']));
    }
};
