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
		Schema::create('business_employees', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("business_id")->index();
			$table->unsignedBigInteger("employee_id")->index();
			$table->timestamps();

			$table->foreign("business_id")->references("id")->on("businesses")->onUpdate("cascade")->onDelete("cascade");
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
		Schema::dropIfExists('business_employees');
	}
};
