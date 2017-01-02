<?php

namespace ModbusTcpClient\Packet\ModbusFunction;


use ModbusTcpClient\Packet\IModbusPacket;
use ModbusTcpClient\Packet\ProtocolDataUnitRequest;
use ModbusTcpClient\Utils\Registers;
use ModbusTcpClient\Utils\Types;

/**
 * Request for Write Multiple Registers (FC=16)
 */
class WriteMultipleRegistersRequest extends ProtocolDataUnitRequest
{
    /**
     * @var array registers (array of bytes)
     */
    private $registers;
    private $registersCount;
    private $registersBytesSize;

    public function __construct($startAddress, array $registers, $unitId = 0, $transactionId = null)
    {
        parent::__construct($startAddress, $unitId, $transactionId);
        $this->registers = $registers;
        $this->registersCount = count($this->registers);
        $this->registersBytesSize = Registers::getRegisterArrayByteSize($this->registers);

        $this->validate();
    }

    public function getFunctionCode()
    {
        return IModbusPacket::WRITE_MULTIPLE_REGISTERS;
    }

    public function getLength()
    {
        return parent::getLength() + (1 + $this->registersBytesSize); // registers count + number of bytes registers need for data
    }

    public function validate()
    {
        parent::validate();
        if (null === $this->registers) {
            throw new \OutOfRangeException('registers is not set');
        }
        if ($this->registersCount === 0 || $this->registersCount > 124) {
            // as request contain 1 byte field 'registersBytesSize' to indicate number of bytes to follow
            // there is no way more than 124 words (124*2 bytes) can be written as this field would overflow
            throw new \OutOfRangeException("registers count out of range (1-124): {$this->registersCount}");
        }
    }

    public function __toString()
    {
        return parent::__toString()
            . Types::toUInt16BE($this->registersCount)
            . Types::toByte($this->registersBytesSize)
            . Registers::getRegisterArrayAsByteString($this->registers);
    }

    public static function parse($binaryString)
    {
        // TODO: Implement parse() method.
    }

    /**
     * @return array
     */
    public function getRegisters()
    {
        return $this->registers;
    }
}