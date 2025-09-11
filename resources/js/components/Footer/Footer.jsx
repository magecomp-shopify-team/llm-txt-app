import { FooterHelp, Text } from "@shopify/polaris";

const Footer = () => {
    const handleChatClick = () => {
        if (typeof $crisp != "undefined") {
            $crisp.push(['do', 'chat:open']);
        }
    }
    const handleDocsClick = () => {
        shopify.toast.show("Coming soon...");
        return;
        const a = document.createElement("a");
        a.href = "https://magecomp.gitbook.io/shopify/apps/kor-order-limit-quantity";
        a.target = "_blank";
        document.body.append(a);
        a.click();
        a.remove();
    }
    return (
        <FooterHelp align="center">
            <Text variant="bodyMd" tone="subdued">
                <span role="button" onClick={handleChatClick} style={{ cursor: "pointer" }}>Chat with us</span> | <span role="button" onClick={handleDocsClick} style={{ cursor: "pointer" }}>Read our documentation</span>
            </Text>
        </FooterHelp>
    )
}

export default Footer;
