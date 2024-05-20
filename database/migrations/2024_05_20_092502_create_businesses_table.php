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
		Schema::create('businesses', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("owner_id");
			$table->string("business_name");
			$table->string("business_type")->nullable();
			$table->timestamps();

			$table->foreign("owner_id")->references("id")->on("users")->onUpdate("cascade")->onDelete("cascade");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('businesses');
	}
};
