/* eslint-disable react/react-in-jsx-scope */
import { render } from '@wordpress/element';
import IsolatedBlockEditor from '@automattic/isolated-block-editor';

const settings = {};
const saveContent = (html) => (console.log(html)); // eslint-disable-line no-console
const loadInitialContent = (parse) => {
  const html = '<!-- wp:paragraph -->\n'
    + '<p>Hello reader!</p>\n'
    + '<!-- /wp:paragraph -->';
  return parse(html);
};

render(
  <IsolatedBlockEditor
    settings={settings}
    onSaveContent={(html) => saveContent(html)}
    onLoad={loadInitialContent}
    onError={() => document.location.reload()}
  />,
  document.querySelector('#mailpoet-email-editor')
);
