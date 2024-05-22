<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			Schema::create('outlet_employees', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger("outlet_id")->index();
				$table->unsignedBigInteger("employee_id")->index();
				$table->timestamps();

				$table->foreign("outlet_id")->references("id")->on("outlets")->onUpdate("cascade")->onDelete("cascade");
				$table->foreign("employee_id")->references("id")->on("users")->onUpdate("cascade")->onDelete("cascade");
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
			Schema::dropIfExists('outlet_employees');
	}
};
