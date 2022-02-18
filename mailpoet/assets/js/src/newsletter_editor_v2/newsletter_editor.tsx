/* eslint-disable react/react-in-jsx-scope */
import { render } from '@wordpress/element';
import { Panel, PanelBody, Button } from '@wordpress/components';
import IsolatedBlockEditor, { DocumentSection, ToolbarSlot } from '@automattic/isolated-block-editor';

const settings = {
  iso: {
    toolbar: {
      inserter: true,
      inspector: true,
      navigation: true,
      toc: true,
      documentInspector: true,
    },
    moreMenu: {
      editor: true,
      fullscreen: true,
      preview: true,
      topToolbar: true,
    },
    allowApi: true,
  },
};
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
  >
    <ToolbarSlot>
      <Button>Save Draft</Button>
      <Button variant="primary">Send</Button>
    </ToolbarSlot>
    <DocumentSection>
      <Panel>
        <PanelBody title="Sending Settings">
          <li>Here comes sending settings</li>
        </PanelBody>
        <PanelBody title="Style Settings">
          <li>Here comes style settings</li>
        </PanelBody>
      </Panel>
    </DocumentSection>
  </IsolatedBlockEditor>,
  document.querySelector('#mailpoet-email-editor')
);
