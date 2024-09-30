<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf_cnpj')->unique()->after('email');
            $table->enum('tipo_usuario', ['cliente', 'lojista'])->after('password');
            $table->decimal('saldo', 10, 2)->default(0.00)->after('tipo_usuario'); // Campo saldo com precisão decimal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cpf_cnpj', 'tipo_usuario', 'saldo']);
        });
    }
};
