<?php

namespace eripDialog\logMessages;


class EdPaymentLogMessage extends EdUserLogMessage
{
	const SOURCE = 'payment';

	public function __toString()
	{
		return json_encode(
			[
				'mode' => static::SOURCE,
				'user_id' => $this->userId,
				'phone' => $this->phone,
				'payment_id' => $this->identifier,
				'to_erip' => $this->request,
				'from_erip' => $this->result
			],
			JSON_UNESCAPED_UNICODE);
	}
}
