<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 8)->default('en');
            $table->string('status', 24)->default('active')->index();
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 24)->default('active')->index();
            $table->string('locale', 8)->default('en');
            $table->json('billing_details')->nullable();
            $table->json('contact_details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32)->default('customer')->index();
            $table->string('status', 24)->default('active');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_platform_role')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 24)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('component')->default('public.templates.premium');
            $table->json('default_configuration')->nullable();
            $table->json('supported_sections')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_premium')->default(false);
            $table->timestamps();
        });

        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32)->default('business')->index();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('company_name')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('tagline')->nullable();
            $table->text('about')->nullable();
            $table->string('preferred_language', 8)->default('en');
            $table->string('status', 24)->default('draft')->index();
            $table->json('style')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id', 'status']);
            $table->index(['owner_id', 'status']);
        });

        Schema::create('profile_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->unique(['profile_id', 'type']);
        });

        Schema::create('contact_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32)->index();
            $table->string('label')->nullable();
            $table->text('value');
            $table->text('url')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('network', 32)->index();
            $table->string('label')->nullable();
            $table->text('url');
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        foreach (['services', 'products'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->decimal('price', 12, 2)->nullable();
                $table->string('currency', 3)->default('QAR');
                $table->boolean('contact_for_price')->default(false);
                $table->string('status', 24)->default('active')->index();
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['profile_id', 'slug']);
            });
        }

        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_24_hours')->default(false);
            $table->timestamps();
            $table->unique(['profile_id', 'weekday']);
        });

        Schema::create('nfc_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('secure_token', 64)->unique();
            $table->string('reference')->nullable()->unique();
            $table->string('status', 24)->default('unassigned')->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('profile_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 40)->index();
            $table->string('visitor_hash', 64)->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->string('referrer_host')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['profile_id', 'event_type', 'occurred_at']);
            $table->index(['organization_id', 'occurred_at']);
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('message');
            $table->boolean('consent')->default(false);
            $table->string('status', 24)->default('new')->index();
            $table->string('source', 32)->default('profile');
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('QAR');
            $table->string('billing_interval', 16)->default('month');
            $table->json('limits')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 24)->default('trial')->index();
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable()->unique();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->nullable();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('QAR');
            $table->string('status', 24)->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->nullableMorphs('subject');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->uuid('request_id')->nullable()->index();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'payments', 'subscriptions', 'plans', 'leads', 'profile_events', 'nfc_cards', 'business_hours', 'products', 'services', 'social_links', 'contact_methods', 'profile_sections', 'profiles', 'templates', 'branches', 'permission_role', 'permissions', 'roles', 'organization_user', 'organizations'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'status']);
        });
    }
};
