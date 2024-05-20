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
		Schema::create('outlets', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("business_id");
			$table->string("outlet_name");
			$table->string("outlet_phone");
			$table->text("outlet_address")->nullable();
			$table->timestamps();

			$table->foreign("business_id")->references("id")->on("businesses")->onUpdate("cascade")->onDelete("cascade");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('outlets');
	}
};
