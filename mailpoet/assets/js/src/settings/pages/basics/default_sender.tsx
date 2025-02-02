import { useEffect, useState } from 'react';
import { Label, Inputs } from 'settings/components';
import { isEmail, t, onChange, setLowercaseValue } from 'common/functions';
import { Input } from 'common/form/input/input';
import { useSetting, useSelector, useAction } from 'settings/store/hooks';
import { SenderEmailAddressWarning } from 'common/sender_email_address_warning.jsx';
import ReactStringReplace from 'react-string-replace';

export function DefaultSender() {
  const isMssActive = useSelector('isMssActive')();
  const [senderName, setSenderName] = useSetting('sender', 'name');
  const [senderEmail, setSenderEmail] = useSetting('sender', 'address');
  const [isAuthorized, setIsAuthorized] = useState(true);
  const [replyToName, setReplyToName] = useSetting('reply_to', 'name');
  const [replyToEmail, setReplyToEmail] = useSetting('reply_to', 'address');
  const setErrorFlag = useAction('setErrorFlag');
  const invalidSenderEmail = senderEmail && !isEmail(senderEmail);
  const invalidReplyToEmail = replyToEmail && !isEmail(replyToEmail);

  const isAuthorizedEmail = (email: string) => {
    if (!isMssActive) {
      return;
    }
    setIsAuthorized(window.mailpoet_authorized_emails.includes(email));
  };
  const updateSenderEmailController = (email: string) => {
    setIsAuthorized(true);
    setSenderEmail(email);
  };
  useEffect(() => {
    setErrorFlag(
      invalidSenderEmail ||
        invalidReplyToEmail ||
        (!isAuthorized && isMssActive),
    );
  }, [
    invalidReplyToEmail,
    invalidSenderEmail,
    setErrorFlag,
    isAuthorized,
    isMssActive,
  ]);
  return (
    <>
      <Label
        title={t('defaultSenderTitle')}
        description={t('defaultSenderDescription')}
        htmlFor="sender-name"
      />
      <Inputs>
        <label htmlFor="sender-name">{t('from')}</label>
        <br />
        <Input
          dimension="small"
          type="text"
          id="sender-name"
          placeholder={t('yourName')}
          data-automation-id="from-name-field"
          value={senderName}
          onChange={onChange(setSenderName)}
        />
        <br />
        <Input
          dimension="small"
          type="text"
          placeholder="from@mydomain.com"
          data-automation-id="from-email-field"
          value={senderEmail}
          onChange={onChange(setLowercaseValue(updateSenderEmailController))}
          onBlur={onChange(isAuthorizedEmail)}
        />
        <br />
        {!isAuthorized && (
          <span className="mailpoet_error_item mailpoet_error">
            {ReactStringReplace(
              t('youNeedToAuthorizeTheEmail'),
              '[email]',
              () => senderEmail,
            )}{' '}
            <a
              className="mailpoet-link"
              href={`https://account.mailpoet.com/authorization?email=${senderEmail}`}
              target="_blank"
              rel="noopener noreferrer"
            >
              {t('authorizeMyEmail')}
            </a>
          </span>
        )}
        {invalidSenderEmail && (
          <span className="mailpoet_error_item mailpoet_error">
            {t('invalidEmail')}
          </span>
        )}
        <div className="regular-text">
          <SenderEmailAddressWarning
            emailAddress={senderEmail}
            mssActive={isMssActive}
          />
        </div>
        <label className="mailpoet-settings-inputs-row" htmlFor="reply_to-name">
          Reply-to
        </label>
        <Input
          dimension="small"
          type="text"
          id="reply_to-name"
          placeholder={t('yourName')}
          data-automation-id="reply_to-name-field"
          value={replyToName}
          onChange={onChange(setReplyToName)}
        />
        <br />
        <Input
          dimension="small"
          type="text"
          placeholder="reply_to@mydomain.com"
          data-automation-id="reply_to-email-field"
          value={replyToEmail}
          onChange={onChange(setLowercaseValue(setReplyToEmail))}
        />
        <br />
        {invalidReplyToEmail && (
          <span className="mailpoet_error_item mailpoet_error">
            {t('invalidEmail')}
          </span>
        )}
      </Inputs>
    </>
  );
}
