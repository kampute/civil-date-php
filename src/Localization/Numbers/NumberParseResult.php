<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization\Numbers;

/**
 * Carries the outcome of parsing a localized number.
 */
final class NumberParseResult
{
    /**
     * Creates a parse result.
     *
     * @param int|null $value Parsed value, or null on failure.
     * @param NumberForm|null $form Parsed number form, or null on failure.
     * @param NumberParseFailureReason|null $failureReason Failure reason, or null on success.
     * @param string|null $invalidWord Invalid word associated with the failure.
     */
    private function __construct(
        private readonly ?int $value,
        private readonly ?NumberForm $form,
        private readonly ?NumberParseFailureReason $failureReason,
        private readonly ?string $invalidWord,
    ) {
    }

    /**
     * Creates a successful parse result.
     *
     * @param int $value Parsed value.
     * @param NumberForm $form Parsed number form.
     *
     * @return self Successful parse result.
     */
    public static function success(int $value, NumberForm $form): self
    {
        return new self($value, $form, null, null);
    }

    /**
     * Creates a failed parse result.
     *
     * @param NumberParseFailureReason $reason Failure reason.
     * @param string|null $word Invalid word associated with the failure.
     *
     * @return self Failed parse result.
     */
    public static function failure(NumberParseFailureReason $reason, ?string $word = null): self
    {
        return new self(null, null, $reason, $word);
    }

    /**
     * Returns whether the parse succeeded.
     *
     * @return bool True when the parse succeeded.
     */
    public function succeeded(): bool
    {
        return $this->failureReason === null;
    }

    /**
     * Returns the parsed value.
     *
     * @return int|null Parsed value, or null on failure.
     */
    public function value(): ?int
    {
        return $this->value;
    }

    /**
     * Returns the parsed number form.
     *
     * @return NumberForm|null Parsed number form, or null on failure.
     */
    public function form(): ?NumberForm
    {
        return $this->form;
    }

    /**
     * Returns why the parse failed.
     *
     * @return NumberParseFailureReason|null Failure reason, or null on success.
     */
    public function failureReason(): ?NumberParseFailureReason
    {
        return $this->failureReason;
    }

    /**
     * Returns the invalid word associated with the failure.
     *
     * @return string|null Invalid word, or null when none was identified.
     */
    public function invalidWord(): ?string
    {
        return $this->invalidWord;
    }
}
